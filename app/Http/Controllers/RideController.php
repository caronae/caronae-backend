<?php

namespace Caronae\Http\Controllers;

use Carbon\Carbon;
use Caronae\Http\Requests\CreateRideRequest;
use Caronae\Http\Resources\MessageResource;
use Caronae\Http\Resources\RideResource;
use Caronae\Models\Campus;
use Caronae\Models\Message;
use Caronae\Models\Ride;
use Caronae\Models\RideUser;
use Caronae\Models\User;
use Caronae\Notifications\RideCanceled;
use Caronae\Notifications\RideFinished;
use Caronae\Notifications\RideJoinRequestAnswered;
use Caronae\Notifications\RideJoinRequested;
use Caronae\Notifications\RideMessageReceived;
use Caronae\Notifications\RideUserLeft;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class RideController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.v1.auth', ['only' => [
            'index',
            'show',
            'store',
            'validateDuplicate',
            'delete', 'deleteAllFromRoutine', 'deleteAllFromUser',
            'listFiltered',
            'requestJoin',
            'getMyActiveRides',
            'leaveRide', 'finishRide',
            'getRidesHistory',
            'getChatMessages',
            'sendChatMessage'
        ]]);

        $this->middleware('api.v1.userBelongsToRide', ['only' => [
            'getChatMessages',
            'sendChatMessage'
        ]]);
    }

    public function index(Request $request)
    {
        $this->validate($request, [
            'zone' => 'string',
            'neighborhoods' => 'string',
            'place' => 'string|max:255',
            'hub' => 'string|max:255',
            'campus' => 'string|max:255',
            'going' => 'boolean',
            'date' => 'string',
            'time' => 'string'
        ]);

        $filters = [];
        if ($request->filled('going'))
            $filters['going'] = $request->going;
        if ($request->filled('neighborhoods'))
            $filters['neighborhoods'] = explode(', ', $request->neighborhoods);
        if ($request->filled('place'))
            $filters['myplace'] = $request->place;
        if ($request->filled('zone'))
            $filters['myzone'] = $request->zone;
        if ($request->filled('campus'))
            $filters['hubs'] = Campus::findByName($request->campus)->hubs()->distinct('center')->pluck('center')->toArray();
        if ($request->filled('hub'))
            $filters['hubs'] = [ $request->hub ];
        else if ($request->filled('hubs'))
            $filters['hubs'] = explode(', ', $request->hubs);

        $limit = 20;
        $rides = Ride::withAvailableSlots()
            ->notFinished()
            ->orderBy('rides.date')
            ->withFilters($filters);

        if ($request->filled('date')) {
            if (!$request->filled('time')) {
                $dateMin = Carbon::createFromFormat('Y-m-d', $request->date)->setTime(0,0,0);
            } else {
                $dateTimeString = $request->date . ' ' . substr($request->time, 0, 5);
                $dateMin = Carbon::createFromFormat('Y-m-d H:i', $dateTimeString);
            }

            $dateMax = $dateMin->copy()->setTime(23,59,59);
            $rides = $rides->whereBetween('date', [$dateMin, $dateMax]);
        } else {
            $rides = $rides->inTheFuture();
        }

        $results = $rides->paginate($limit);
        $results->each(function ($ride) {
            $ride->driver = $ride->driver();
        });

        return $results;
    }
    
    public function show(Ride $ride)
    {
        RideResource::withoutWrapping();
        return (new RideResource($ride))->withAvailableSlots();
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
        $user = $request->currentUser;

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
    
    public function validateDuplicate(Request $request)
    {
        $this->validate($request, [
            'date' => 'required|date_format:d/m/Y',
            'time' => 'required|date_format:H:i:s',
            'going' => 'required|boolean'
        ]);

        $dateTime = Carbon::createFromFormat('d/m/Y H:i:s', $request->input('date') . ' ' . $request->input('time'));
        $dateMin = $dateTime->copy()->setTime(0,0,0);
        $dateMax = $dateTime->copy()->setTime(23,59,59);

        $ridesFound = $request->currentUser->rides()
            ->whereBetween('date', [$dateMin, $dateMax])
            ->where('going', $request->input('going'))
            ->get();

        if (count($ridesFound) > 0) {
            $valid = false;

            $duplicated = false;
            $ridesFound->each(function ($ride) use ($dateTime, &$duplicated) {
                if (abs($dateTime->diffInMinutes($ride->date)) <= 30) {
                    $duplicated = true;
                    return false;
                }
            });

            if ($duplicated) {
                $status = 'duplicate';
                $message = 'The user has already offered a ride on the specified date.';
            } else {
                $status = 'possible_duplicate';
                $message = 'The user has already offered a ride too close to the specified date.';
            }
        } else {
            $valid = true;
            $status = 'valid';
            $message = 'No conflicting rides were found close to the specified date.';
        }

        return [
            'valid' => $valid,
            'status' => $status,
            'message' => $message
        ];
    }

    public function delete(Request $request, $rideId)
    {
        return DB::transaction(function() use ($request, $rideId) {
            $user = $request->currentUser;
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
            $user = $request->currentUser;

            $matchThese = ['status' => 'driver', 'going' => $going, 'done' => false];
            $rideIdList = $user->rides()->where($matchThese)->pluck('ride_id')->toArray();

            RideUser::whereIn('ride_id', $rideIdList)->delete(); //delete all relationships with the rides first
            Ride::whereIn('id', $rideIdList)->forceDelete();
        });
    }

    public function deleteAllFromRoutine(Request $request, $routineId)
    {
        return DB::transaction(function() use ($request, $routineId) {
            $user = $request->currentUser;

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

    public function requestJoin(Request $request)
    {
        $this->validate($request, [
            'rideId' => 'required|int'
        ]);

        $user = $request->currentUser;
        $rideID = $request->rideId;

        //if a relationship already exists, do not create another one
        $previousRequest = $user->rides()->where('rides.id', $rideID)->first();
        if ($previousRequest != null) {
            return ['message' => 'Relationship between user and ride already exists as ' . $previousRequest->pivot->status];
        }

        //save relationship between ride and user
        $user->rides()->attach($rideID, ['status' => 'pending']);

        //send notification
        $ride = Ride::find($rideID);
        $driver = $ride->driver();
        $driver->notify(new RideJoinRequested($ride, $user));

        return ['message' => 'Request sent.'];
    }

    public function getRequesters($rideId)
    {
        $ride = Ride::find($rideId);
        if ($ride == null) {
            return $this->error('ride not found with id = ' . $rideId, 400);
        }

        return $ride->users()->where('status', 'pending')->get();
    }

    public function answerJoinRequest(Request $request)
    {
        //find existing relationship which should be pending
        $matchThese = ['ride_id' => $request->rideId, 'user_id' => $request->userId, 'status' => 'pending'];
        $rideUser = RideUser::where($matchThese)->first();
        if ($rideUser == null) {
            return $this->error('Ride request not found.', 400);
        }

        $rideUser->status = $request->accepted ? 'accepted' : 'refused';
        $rideUser->save();

        //send notification
        $ride = Ride::find($request->rideId);
        $user = User::find($request->userId);
        $user->notify(new RideJoinRequestAnswered($ride, $request->accepted));

        return ['message' => 'Request answered.'];
    }

    public function getMyActiveRides(Request $request)
    {
        $user = $request->currentUser;

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

    public function leaveRide(Request $request)
    {
        $user = $request->currentUser;
        $rideID = $request->rideId;

        $rideUser = RideUser::where(['ride_id' => $rideID, 'user_id' => $user->id])->first();
        $ride = Ride::find($rideID);

        if ($rideUser->status == 'driver') {
            $rideCanceledNotification = new RideCanceled($ride, $user);
            $riders = $ride->riders()->get();
            foreach ($riders as $rider) {
                $rider->notify($rideCanceledNotification);
            }

            RideUser::where('ride_id', $rideID)->delete();

            $ride->delete();
        } else {
            $rideUser->status = 'quit';
            $rideUser->save();

            $ride->driver()->notify(new RideUserLeft($ride, $user));
        }

        return ['message' => 'Left ride.'];
    }

    public function finishRide(Request $request)
    {
        $ride = $request->currentUser->rides()->where(['rides.id' => $request->rideId, 'status' => 'driver'])->first();
        if ($ride == null) {
            return $this->error('User is not the driver of this ride', 403);
        }

        if ($ride->date->isFuture()) {
            return $this->error('A ride in the future cannot be marked as finished', 403);
        }

        $ride->done = true;
        $ride->save();

        $rideFinishedNotification = new RideFinished($ride, $request->currentUser);
        $riders = $ride->riders()->get();
        foreach ($riders as $rider) {
            $rider->notify($rideFinishedNotification);
        }

        return ['message' => 'Ride finished.'];
    }

    public function getRidesHistory(Request $request)
    {
        $user = $request->currentUser;
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

    public function getChatMessages(Request $request, Ride $ride)
    {
        $this->validate($request, [
            'since' => 'date'
        ]);

        if ($request->since) {
            $messages = $ride->messages()->where('created_at', '>', $request->since)->orderBy('created_at')->get();
        } else {
            $messages = $ride->messages()->orderBy('created_at')->get();
        }

        return [
            'messages' => MessageResource::collection($messages)
        ];
    }

    public function sendChatMessage(Request $request, Ride $ride)
    {
        $this->validate($request, [
            'message' => 'required'
        ]);

        $message = Message::create([
            'ride_id' => $ride->id,
            'user_id' => $request->currentUser->id,
            'body' => $request->message
        ]);
        $notification = new RideMessageReceived($message);
        
        $subscribers = $ride->users()
            ->whereIn('status', ['accepted', 'driver'])
            ->where('user_id', '!=', $request->currentUser->id)
            ->get();
        $subscribers->each(function($user) use ($notification) {
            $user->notify($notification);
        });

        return response()->json([
            'message' => 'Message sent.',
            'id' => $message->id
        ], 201);
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
