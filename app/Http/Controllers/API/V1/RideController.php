<?php

namespace Caronae\Http\Controllers\API\v1;

use Carbon\Carbon;
use Caronae\Http\Controllers\BaseController;
use Caronae\Http\Requests\CreateRideRequest;
use Caronae\Http\Requests\RideListRequest;
use Caronae\Http\Resources\RideResource;
use Caronae\Models\Ride;
use Caronae\Models\RideUser;
use Caronae\Models\User;
use Caronae\Notifications\RideCanceled;
use Caronae\Notifications\RideFinished;
use Caronae\Notifications\RideJoinRequestAnswered;
use Caronae\Notifications\RideJoinRequested;
use Caronae\Notifications\RideUserLeft;
use Caronae\Services\ValidateDuplicateService;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class RideController extends BaseController
{
    
    public function index(RideListRequest $request)
    {
        $limit = 20;
        $rides = Ride::withAvailableSlots()
            ->notFinished()
            ->orderBy('rides.date')
            ->withFilters($request->filters())
            ->withInstitution($request->user()->institution);

        if ($request->dateRange()) {
            $rides = $rides->whereBetween('date', $request->dateRange());
        } else {
            $rides = $rides->inTheFuture();
        }

        $results = $rides->paginate($limit);
        $results->each(function ($ride) {
            $ride->driver = $ride->driver();
        });

        return $results;
    }
    
    public function show(Ride $ride, Request $request)
    {
        RideResource::withoutWrapping();

        if ($request->user()->belongsToRide($ride)) {
            $ride->load('riders');
        }

        $rideResource = new RideResource($ride);
        return $rideResource->withAvailableSlots();
    }

    public function showWeb($id)
    {
        try {
            $ride = Ride::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->view('rides.notFound')->setStatusCode(404);
        }

        $title = $ride->title . ' | ' . $ride->date->format('H:i');
        $driver = $ride->driver()->name;
        $deepLinkUrl = 'caronae://carona/' . $ride->id;
        return view('rides.showWeb', ['title' => $title, 'driver' => $driver, 'deepLinkUrl' => $deepLinkUrl]);
    }

    public function store(CreateRideRequest $request)
    {
        $user = $request->user();

        $validateDuplicateService = new ValidateDuplicateService($user, $request->all()['date'], $request->get('going'));
        $resultValidation = $validateDuplicateService->validate();

        if (!$resultValidation['valid']) {
            return response()->json([
                'message' => $resultValidation['message'],
            ], 422);
        }

        $ridesCreated = collect();
        DB::transaction(function() use ($request, $user, &$ridesCreated) {
            $ride = Ride::create($request->all());
            $ride->users()->attach($user->id, ['status' => 'driver']);
            $ridesCreated[] = $ride;

            if ($request->isRoutine()) {
                $repeats_until = $request->getRoutineEndDate();

                $ride->repeats_until = $repeats_until;
                $ride->week_days = $request->week_days;
                $ride->routine_id = $ride->id;
                $ride->save();

                $repeating_dates = $this->recurringDates($ride->date, $repeats_until, $ride->week_days);

                foreach ($repeating_dates as $date) {
                    if ($date == $ride->date) continue;

                    $repeating_ride = new Ride();
                    $repeating_ride->fill($request->all());
                    $repeating_ride->date = $date;
                    $repeating_ride->week_days = $ride->week_days;
                    $repeating_ride->routine_id = $ride->id;
                    $repeating_ride->save();

                    $ridesCreated[] = $repeating_ride;

                    $repeating_ride->users()->attach($user->id, ['status' => 'driver']);
                }
            }
        });

        RideResource::withoutWrapping();
        return RideResource::collection($ridesCreated)->response()->setStatusCode(201);
    }
    
    public function validateDuplicate(ValidateDuplicateService $validateDuplicateService)
    {
        return $validateDuplicateService->validate();
    }

    public function delete(Request $request, $rideId)
    {
        return DB::transaction(function() use ($request, $rideId) {
            $user = $request->user();
            $ride = $user->rides()->where(['rides.id' => $request->rideId, 'status' => 'driver'])->first();
            if ($ride == null) {
                return $this->error('User is not the driver on this ride or ride does not exist.', 403);
            }

            RideUser::where('ride_id', $rideId)->delete(); //delete all relationships with this ride first
            $ride->forceDelete();
        });
    }

    public function deleteAllFromUser(Request $request, $userId, $going)
    {
        return DB::transaction(function() use ($request, $going) {
            $user = $request->user();

            $matchThese = ['status' => 'driver', 'going' => $going, 'done' => false];
            $rideIdList = $user->rides()->where($matchThese)->pluck('ride_id')->toArray();

            RideUser::whereIn('ride_id', $rideIdList)->delete(); //delete all relationships with the rides first
            Ride::whereIn('id', $rideIdList)->forceDelete();
        });
    }

    public function deleteAllFromRoutine(Request $request, $routineId)
    {
        return DB::transaction(function() use ($request, $routineId) {
            $user = $request->user();

            $matchThese = ['routine_id' => $routineId, 'done' => false];
            $rideIdList = Ride::where($matchThese)->pluck('id')->toArray();

            if ($rideIdList == null || empty($rideIdList)) {
                return $this->error('No rides found with this routine id.', 400);
            }
            $matchThese2 = ['ride_id' => $rideIdList[0], 'user_id' => $user->id, 'status' => 'driver'];
            if (RideUser::where($matchThese2)->count() < 1) {
                return $this->error('User is not the driver on this ride.', 403);
            }

            RideUser::whereIn('ride_id', $rideIdList)->delete(); //delete all relationships with the rides first
            Ride::where($matchThese)->forceDelete();

        });
    }

    /**
     * @deprecated
     */
    public function listFiltered(Request $request)
    {
        $locations = explode(', ', $request->location);

        //location can be zones or neighborhoods, check if first array position is a zone or a neighborhood
        if ($locations[0] == 'Centro' || $locations[0] == 'Zona Sul' || $locations[0] == 'Zona Oeste' || $locations[0] == 'Zona Norte' || $locations[0] == 'Baixada' || $locations[0] == 'Grande Niterói' || $locations[0] == 'Outros') {
            $locationColumn = 'myzone';
        } else {
            $locationColumn = 'neighborhood';
        }

        $dateMin = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . substr($request->time, 0, 5));
        $dateMax = $dateMin->copy()->setTime(23,59,59);

        $rides = Ride::whereBetween('date', [$dateMin, $dateMax])
            ->where('done', false)
            ->where('going', $request->go)
            ->whereIn($locationColumn, $locations);

        if ($request->filled('center')) {
            $rides = $rides->where('hub', 'LIKE', $request->input('center') . '%');
        }

        $rides = $rides->get();

        $results = [];
        foreach($rides as $ride) {
            //check if ride is full
            if ($ride->users()->whereIn('status', ['pending', 'accepted'])->count() < $ride->slots) {
                //gets the driver
                $driver = $ride->users()->where('status', 'driver')->first();
                //if could not find driver, he's probably been banned, so skip ride
                if (!$driver) continue;

                $resultRide = $ride;
                $resultRide->driver = $driver;

                $results[] = $resultRide;
            }
        }

        return $results;
    }

    public function getRequests(Ride $ride)
    {
        return $ride->requests;
    }

    public function createRequest(Ride $ride = null, Request $request)
    {
        if ($ride == null) {
            $this->validate($request, [
                'rideId' => 'required|int'
            ]);
            $ride = Ride::find($request->input('rideId'));
        }

        $user = $request->user();

        if (!$ride->institution->is($user->institution)) {
            return $this->error('You can\'t request to participate in a ride from another institution.', 403);
        }

        //if a relationship already exists, do not create another one
        $previousRequest = $ride->users()->where('users.id', $user->id);
        if ($previousRequest->exists()) {
            return ['message' => 'Ride request already exists.'];
        }

        $ride->users()->attach($user, ['status' => 'pending']);

        $driver = $ride->driver();
        $driver->notify(new RideJoinRequested($ride, $user));

        return ['message' => 'Request created.'];
    }

    public function updateRequest(Ride $ride = null, Request $request)
    {
        if ($ride == null) {
            $this->validate($request, [
                'rideId' => 'required|int'
            ]);
            $ride = Ride::find($request->input('rideId'));
        }

        $this->validate($request, [
            'userId' => 'required|int',
            'accepted' => 'required|boolean',
        ]);

        $user = User::find($request->input('userId'));
        $previousRequest = $ride->users()->where(['users.id' => $user->id, 'status' => 'pending']);
        if (!$previousRequest->exists()) {
            return $this->error('Ride request not found.', 400);
        }

        $status = $request->input('accepted') ? 'accepted' : 'refused';
        $ride->users()->updateExistingPivot($user->id, ['status' => $status]);

        $user->notify(new RideJoinRequestAnswered($ride, $request->input('accepted')));

        return ['message' => 'Request updated.'];
    }

    public function getMyActiveRides(Request $request)
    {
        $user = $request->user();

        //active rides have 'driver' or 'accepted' status
        $rides = $user->rides()->whereIn('status', ['driver', 'accepted'])->where('done', false)->get();

        $resultArray = array();
        foreach($rides as $ride) {
            $resultRide = $ride;

            $riders = $ride->users()->whereIn('status', ['driver', 'accepted'])->get();
            //if count == 1 driver is the only one on the ride, therefore ride is not active
            if (count($riders) == 1) continue;

            //now we need to put the driver in the beginning of the array
            $resultRiders = [];
            foreach($riders as $rider) {
                $riderStatus = $rider->pivot->status;

                if ($riderStatus == 'driver') {
                    $resultRide->driver = $rider;
                } else {
                    $resultRiders[] = $rider;
                }
            }

            $resultRide->riders = $resultRiders;
            $resultArray[] = $resultRide;
        }

        return $resultArray;
    }

    public function leaveRide(Ride $ride = null, Request $request)
    {
        $user = $request->user();
        if ($ride == null) {
            $ride = Ride::find($request->rideId);
        }

        $rideUser = RideUser::where(['ride_id' => $ride->id, 'user_id' => $user->id])->first();

        if ($rideUser->status == 'driver') {
            $rideCanceledNotification = new RideCanceled($ride, $user);
            $ride->riders->each->notify($rideCanceledNotification);
            $ride->requests->each->notify($rideCanceledNotification);

            RideUser::where('ride_id', $ride->id)->delete();

            $ride->delete();
        } else {
            $rideUser->status = 'quit';
            $rideUser->save();

            $ride->driver()->notify(new RideUserLeft($ride, $user));
        }

        return ['message' => 'Left ride.'];
    }

    public function finishRide(Ride $ride = null, Request $request)
    {
        if ($ride == null) {
            $ride = $request->user()->rides()->where(['rides.id' => $request->rideId, 'status' => 'driver'])->first();
            if ($ride == null) {
                return $this->error('User is not the driver of this ride', 403);
            }
        }

        if ($ride->date->isFuture()) {
            return $this->error('A ride in the future cannot be marked as finished', 403);
        }

        $ride->done = true;
        $ride->save();

        $rideFinishedNotification = new RideFinished($ride, $request->user());
        $riders = $ride->riders()->get();
        $riders->each->notify($rideFinishedNotification);

        return ['message' => 'Ride finished.'];
    }

    /**
     * @deprecated
     */
    public function getRidesHistory(Request $request)
    {
        $user = $request->user();
        $rides = $user->rides()->where('done', true)->whereIn('status', ['driver', 'accepted'])->get();

        $resultJson = [];
        foreach ($rides as $ride) {
            $riders = $ride->users()->whereIn('status', ['driver', 'accepted'])->get();

            $resultRide = $ride;

            $resultRiders = [];
            foreach($riders as $rider) {
                $riderStatus = $rider->pivot->status;

                if ($riderStatus == 'driver') {
                    $resultRide->driver = $rider;
                } else {
                    $resultRiders[] = $rider;
                }
            }

            $resultRide->riders = $resultRiders;
            $resultRide->feedback = $resultRide->pivot->feedback;
            $resultJson[] = $resultRide;
        }

        return $resultJson;
    }

    /**
     * @deprecated
     */
    public function getRidesHistoryCount($userId)
    {
        $user = User::find($userId);
        $offeredCount = $user->rides()->where('done', true)->where('status', 'driver')->count();
        $takenCount = $user->rides()->where('done', true)->where('status', 'accepted')->count();

        return [
            'offeredCount' => $offeredCount,
            'takenCount' => $takenCount
        ];
    }

    public function saveFeedback(Request $request)
    {
        $matchThese = ['ride_id' => $request->rideId, 'user_id' => $request->userId];
        $ride_user = RideUser::where($matchThese)->first();

        if ($ride_user == null) {
            return $this->error('relationship between user with id ' . $request->userId . ' and ride with id '. $request->rideId . ' does not exist or ride does not exist', 400);
        }

        $ride_user->feedback = $request->feedback;
        $ride_user->save();
    }


    /// Helper methods

    protected function recurringDates($startDate, $endDate, $weekDaysString)
    {
        $weekDays = $this->weekDaysStringToRecurrString($weekDaysString);

        $recurringRule = new \Recurr\Rule();
        $recurringRule->setFreq('WEEKLY');
        $recurringRule->setByDay($weekDays);
        $recurringRule->setStartDate($startDate);
        $recurringRule->setUntil($endDate);

        $transformer = new \Recurr\Transformer\ArrayTransformer();
        $events = $transformer->transform($recurringRule);

        $dates = [];
        foreach ($events as $event) {
            $dates[] = $event->getStart();
        }

        return $dates;
    }

    protected function weekDaysStringToRecurrString($weekDaysString)
    {
        $weekDaysTable = [
            '0' => 'SU',
            '1' => 'MO',
            '2' => 'TU',
            '3' => 'WE',
            '4' => 'TH',
            '5' => 'FR',
            '6' => 'SA',
            '7' => 'SU'
        ];

        $weekDays = explode(',', $weekDaysString);
        for ($i=0; $i < count($weekDays); $i++) {
            $number = $weekDays[$i];
            $weekDays[$i] = $weekDaysTable[$number];
        }

        return $weekDays;
    }

}
