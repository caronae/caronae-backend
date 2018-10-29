<?php

namespace Caronae\Http\Controllers\API\v1;

use Caronae\Http\Controllers\BaseController;
use Caronae\Http\Requests\CreateRideRequest;
use Caronae\Http\Requests\RideListRequest;
use Caronae\Http\Resources\RideResource;
use Caronae\Models\Ride;
use Caronae\Models\RideUser;
use Caronae\Models\User;
use Caronae\Notifications\RideCanceled;
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
        DB::transaction(function () use ($request, $user, &$ridesCreated) {
            $ride = Ride::create($request->all());
            $ride->users()->attach($user->id, ['status' => 'driver']);
            $ridesCreated[] = $ride;

            if ($request->isRoutine()) {
                $repeats_until = $request->getRoutineEndDate();

                $ride->repeats_until = $repeats_until;
                $ride->week_days = $request->week_days;
                $ride->routine_id = $ride->id;
                $ride->save();

                $repeating_dates = recurringDates($ride->date, $repeats_until, $ride->week_days);

                foreach ($repeating_dates as $date) {
                    if ($date == $ride->date) {
                        continue;
                    }

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

    public function deleteAllFromRoutine(Request $request, $routineId)
    {
        return DB::transaction(function () use ($request, $routineId) {
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

    public function getRequests(Ride $ride)
    {
        return $ride->requests;
    }

    public function createRequest(Ride $ride, Request $request)
    {
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

    public function updateRequest(Ride $ride, Request $request)
    {
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

    public function leaveRide(Ride $ride, Request $request)
    {
        $user = $request->user();
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

    public function finishRide(Ride $ride, Request $request)
    {
        if ($ride->date->isFuture()) {
            return $this->error('A ride in the future cannot be marked as finished', 403);
        }

        $ride->done = true;
        $ride->save();

        return ['message' => 'Ride finished.'];
    }
}
