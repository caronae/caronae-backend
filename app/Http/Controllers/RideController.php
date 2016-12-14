<?php

namespace Caronae\Http\Controllers;

use Caronae\ExcelExport\ExcelExporter;
use Caronae\Http\Requests;
use Caronae\Http\Requests\RankingRequest;
use Caronae\Models\Ride;
use Caronae\Models\RideUser;
use Caronae\Models\User;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use DateTimeZone;
use DB;
use Caronae\Services\PushNotificationService;
use Illuminate\Http\Request;

class RideController extends Controller
{
    protected $push;

    /**
     * Instantiate a new RideController instance.
     *
     * @return void
     */
    public function __construct(PushNotificationService $push)
    {
        $this->push = $push;

        $this->middleware('api.v1.auth', ['only' => [
            'store',
            'validateDuplicate',
            'delete', 'deleteAllFromRoutine', 'deleteAllFromUser',
            'listAll', 'listFiltered',
            'requestJoin',
            'getMyActiveRides',
            'leaveRide', 'finishRide',
            'getRidesHistory',
            'sendChatMessage'
        ]]);

        $this->middleware('api.v1.userBelongsToRide', ['only' => [
            'sendChatMessage'
        ]]);
    }

    public function index()
    {
        $limit = 50;
        $timezone = new DateTimeZone('America/Sao_Paulo');
        $minDate = (new DateTime('now', $timezone))->format('Y-m-d H:i:s');

        $rides = Ride::leftjoin('ride_user', 'rides.id', '=', 'ride_user.ride_id')
            ->select('rides.*')
            ->where('rides.date', '>=', $minDate)
            ->where('rides.done', 'false')
            ->whereIn('ride_user.status', ['pending','accepted','driver'])
            ->groupBy('rides.id')
            ->having(DB::raw('count(ride_user.user_id)-1'), '<', DB::raw('rides.slots'))
            ->orderBy('rides.date')
            ->paginate($limit);

        $results = [];
        foreach($rides as $ride) {
            unset($ride->done);
            $ride->driver = $ride->driver();
            $results[] = $ride;
        }

        return $results;
    }

    public function store(Request $request)
    {
        $user = $request->user;
        $decode = json_decode($request->getContent());

        $rides_created = [];
        DB::transaction(function() use ($decode, $user, &$rides_created) {
            //create new ride and save it
            $ride = new Ride();
            $ride->myzone = $decode->myzone;
            $ride->neighborhood = $decode->neighborhood;
            $ride->place = $decode->place;
            $ride->route = $decode->route;
            $ride->date = Carbon::createFromFormat('d/m/Y H:i', $decode->mydate . ' ' . substr($decode->mytime, 0, 5));
            $ride->slots = $decode->slots;
            $ride->hub = $decode->hub;
            $ride->description = $decode->description;
            $ride->going = $decode->going;

            $ride->save();
            $rides_created[] = $ride;

            // save relationship between ride and user
            $ride->users()->attach($user->id, ['status' => 'driver']);

            // check if the ride is recurring. if so, there will be a field 'repeats_until'
            // and a field 'week_days' with the repeating days (1->monday, 2->tuesday, ..., 7->sunday)
            if (!empty($decode->repeats_until) && is_string($decode->repeats_until)) {
                $repeats_until = DateTime::createFromFormat('d/m/Y', $decode->repeats_until);
                $ride->repeats_until = $repeats_until->format('Y-m-d');
                $ride->week_days = $decode->week_days;

                $repeating_dates = $this->recurringDates($ride->date, $repeats_until, $ride->week_days);

                foreach ($repeating_dates as $date) {
                    // Skip if it's the date of the original Ride
                    if ($date == $ride->date) continue;

                    // Creating repeating Ride objects. All fields are the same except for
                    // the date - which will have a new generated date - and a foreign key
                    // to the original Ride (routine_id).
                    $repeating_ride = new Ride();
                    $repeating_ride->myzone = $ride->myzone;
                    $repeating_ride->neighborhood = $ride->neighborhood;
                    $repeating_ride->place = $ride->place;
                    $repeating_ride->route = $ride->route;
                    $repeating_ride->date = $date; // New date
                    $repeating_ride->slots = $ride->slots;
                    $repeating_ride->hub = $ride->hub;
                    $repeating_ride->description = $ride->description;
                    $repeating_ride->going = $ride->going;
                    $repeating_ride->week_days = $ride->week_days;
                    $repeating_ride->routine_id = $ride->id; // References the original ride which originated this ride

                    $repeating_ride->save();

                    $rides_created[] = $repeating_ride;

                    // Saving the relationship between ride and user
                    $repeating_ride->users()->attach($user->id, ['status' => 'driver']);
                }

                $ride->routine_id = $ride->id;
                $ride->save();
            } else {
                $ride->routine_id = NULL;
                $ride->week_days = NULL;
                $ride->repeats_until = NULL;
            }
        });

        if (empty($rides_created)) {
            return response()->json(['error'=>'No rides were created.'], 204);
        }

        return $rides_created;
    }
    
    public function validateDuplicate(Request $request)
    {
        $this->validate($request, [
            'date' => 'required|date_format:d/m/Y',
            'time' => 'required|date_format:H:i:s',
            'going' => 'required|boolean'
        ]);

        $dateTime = Carbon::createFromFormat('d/m/Y H:i:s', $request->input('date') . ' ' . $request->input('time'));
        $date = $dateTime->format('Y-m-d');
        $timeMin = $dateTime->copy()->subHours(2)->format('H:i:s'); // check for rides a few hours before the time
        $timeMax = $dateTime->copy()->addHours(2)->format('H:i:s'); // check for rides a few hours after the time

        $ridesFound = $request->user->rides()
            ->where([DB::raw('date::DATE') => $date, 'going' => $request->input('going')])
            ->whereBetween(DB::raw('date::TIME'), [$timeMin, $timeMax])
            ->exists();

        if ($ridesFound) {
            $valid = false;
            $status = 'possible_duplicate';
            $message = 'The user has already offered a ride too close to the specified date.';
        } else {
            $valid = true;
            $status = 'valid';
            $message = 'No conflicting rides were found close to the specified date.';
        }

        return response()->json([
            'valid' => $valid,
            'status' => $status,
            'message' => $message
        ]);
    }

    public function delete(Request $request, $rideId)
    {
        return DB::transaction(function() use ($request, $rideId) {
            $user = $request->user;
            $ride = $user->rides()->where(['rides.id' => $request->rideId, 'status' => 'driver'])->first();
            if ($ride == null) {
                return response()->json(['error'=>'User is not the driver on this ride or ride does not exist.'], 403);
            }

            RideUser::where('ride_id', $rideId)->delete(); //delete all relationships with this ride first
            $ride->forceDelete();
        });
    }

    public function deleteAllFromUser(Request $request, $userId, $going)
    {
        return DB::transaction(function() use ($request, $going) {
            $user = $request->user;

            $matchThese = ['status' => 'driver', 'going' => $going, 'done' => false];
            $rideIdList = $user->rides()->where($matchThese)->pluck('ride_id')->toArray();

            RideUser::whereIn('ride_id', $rideIdList)->delete(); //delete all relationships with the rides first
            Ride::whereIn('id', $rideIdList)->forceDelete();
        });
    }

    public function deleteAllFromRoutine(Request $request, $routineId)
    {
        return DB::transaction(function() use ($request, $routineId) {
            $user = $request->user;

            $matchThese = ['routine_id' => $routineId, 'done' => false];
            $rideIdList = Ride::where($matchThese)->pluck('id')->toArray();

            if ($rideIdList == null || empty($rideIdList)) {
                return response()->json(['error'=>'No rides found with this routine id.'], 400);
            }
            $matchThese2 = ['ride_id' => $rideIdList[0], 'user_id' => $user->id, 'status' => 'driver'];
            if (RideUser::where($matchThese2)->count() < 1) {
                return response()->json(['error'=>'User is not the driver on this ride.'], 403);
            }

            RideUser::whereIn('ride_id', $rideIdList)->delete(); //delete all relationships with the rides first
            Ride::where($matchThese)->forceDelete();

        });
    }

    public function listFiltered(Request $request)
    {
        $decode = json_decode($request->getContent());

        //locations will come as a string divided by ", ", explode the string into an array
        $locations = explode(", ", $decode->location);

        //location can be zones or neighborhoods, check if first array position is a zone or a neighborhood
        if ($locations[0] == "Centro" || $locations[0] == "Zona Sul" || $locations[0] == "Zona Oeste" || $locations[0] == "Zona Norte" || $locations[0] == "Baixada" || $locations[0] == "Grande Niterói" || $locations[0] == "Outros") {
            $locationColumn = 'myzone';//if location is filtered by zone, query by 'myzone' column
        } else {
            $locationColumn = 'neighborhood';//if location is filtered by neighborhood, query by 'neighborhood' column
        }
        $matchThese = ['going' => $decode->go, DB::raw('date::DATE') => $decode->date, 'done' => false];

        //query the rides
        if (empty($decode->center)) {
            $rides = Ride::where($matchThese)->whereIn($locationColumn, $locations)->where(DB::raw('date::TIME'), '>=', $decode->time)->get();
        } else {
            $rides = Ride::where($matchThese)->whereIn($locationColumn, $locations)->where(DB::raw('date::TIME'), '>=', $decode->time)->where('hub', 'LIKE', "$decode->center%")->get();
        }

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

        $user = $request->user;
        $rideID = $request->rideId;

        //if a relationship already exists, do not create another one
        $previousRequest = $user->rides()->where('rides.id', $rideID)->first();
        if ($previousRequest != null) {
            return response()->json(['message' => 'Relationship between user and ride already exists as ' . $previousRequest->pivot->status]);
        }

        //save relationship between ride and user
        $user->rides()->attach($rideID, ['status' => 'pending']);

        //send notification
        $notification = [
            'message' => 'Sua carona recebeu uma solicitação',
            'msgType' => 'joinRequest',
            'rideId'  => $rideID
        ];
        $driver = Ride::find($rideID)->driver();
        $this->push->sendNotificationToUser($driver, $notification);

        return response()->json(['message' => 'Request sent.']);
    }

    public function getRequesters($rideId)
    {
        $ride = Ride::find($rideId);
        if ($ride == null) {
            return response()->json(['error' => 'ride not found with id = ' . $rideId], 400);
        }

        return $ride->users()->where('status', 'pending')->get();
    }

    public function answerJoinRequest(Request $request)
    {
        //find existing relationship which should be pending
        $matchThese = ['ride_id' => $request->rideId, 'user_id' => $request->userId, 'status' => 'pending'];
        $rideUser = RideUser::where($matchThese)->first();
        if ($rideUser == null) {
            return response()->json(['error' => 'Ride request not found.'], 400);
        }

        $rideUser->status = $request->accepted ? 'accepted' : 'refused';
        $rideUser->save();

        //send notification
        $user = User::find($request->userId);
        $notification = [
            'message' => $request->accepted ? 'Você foi aceito em uma carona =)' : 'Você foi recusado em uma carona =(',
            'msgType' => $request->accepted ? 'accepted' : 'refused',
            'rideId'  => $request->rideId
        ];
        if ($user == null) die('err');
        $this->push->sendNotificationToUser($user, $notification);

        return response()->json(['message' => 'Request answered.']);
    }

    public function getMyActiveRides(Request $request)
    {
        $user = $request->user;

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
        $user = $request->user;
        $rideID = $request->rideId;

        $matchThese = ['ride_id' => $rideID, 'user_id' => $user->id];
        $rideUser = RideUser::where($matchThese)->first();//get relationship
        if ($rideUser->status == 'driver') {//if user is the driver the ride needs to be deleted
            $ride = Ride::find($rideID);

            RideUser::where('ride_id', $rideID)->delete();//delete all relationships to this ride
            $ride->delete();

            //send notification to riders on that ride
            $notification = [
                'message' => 'Um motorista cancelou uma carona ativa sua',
                'msgType' => 'cancelled',
                'rideId'  => $rideID
            ];

            foreach ($ride->riders() as $user) {
                $this->push->sendNotificationToUser($user, $notification);
            }

        } else {//if user is not the driver, just set relationship as quit
            $rideUser->status = 'quit';
            $rideUser->save();

            $notification = [
                'message' => 'Um caronista desistiu de sua carona',
                'msgType' => 'quitter'
            ];
            $driver = Ride::find($rideID)->driver();
            $this->push->sendNotificationToUser($driver, $notification);
        }

        return response()->json(['message' => 'Left ride.']);
    }

    public function finishRide(Request $request)
    {
        //check if the current user is the driver of the ride
        $ride = $request->user->rides()->where(['rides.id' => $request->rideId, 'status' => 'driver'])->first();
        if ($ride == null) {
            return response()->json(['error' => 'User is not the driver of this ride'], 403);
        }

        $ride->done = true;
        $ride->save();

        //send notification to riders on that ride
        $notification = [
            'message' => 'Um motorista concluiu uma carona ativa sua',
            'msgType' => 'finished',
            'rideId' => $request->rideId
        ];

        foreach ($ride->riders() as $user) {
            $this->push->sendNotificationToUser($user, $notification);
        }

        return response()->json(['message' => 'Ride finished.']);
    }

    public function getRidesHistory(Request $request)
    {
        $user = $request->user;
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
            return response()->json(['error'=>'relationship between user with id ' . $request->userId . ' and ride with id '. $request->rideId . ' does not exist or ride does not exist'], 400);
        }

        $ride_user->feedback = $request->feedback;
        $ride_user->save();
    }

    public function sendChatMessage(Request $request, Ride $ride)
    {
        $user = $request->user;
        $message = $request->input('message');

        $data = [
            'message' => $message,
            'rideId' => $ride->id,
            'msgType' => 'chat',
            'senderName' => $user->name,
            'senderId' => $user->id,
            'time' => Carbon::now()->toDateTimeString()
        ];

        $this->push->sendDataToRideMembers($ride, $data);
        return response()->json(['message' => 'Message sent.']);
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
