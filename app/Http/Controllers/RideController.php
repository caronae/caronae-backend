<?php

namespace App\Http\Controllers;

use App\ExcelExport\ExcelExporter;
use App\Http\Requests;
use App\Http\Requests\RankingRequest;
use App\Ride;
use App\RideUser;
use App\User;
use Carbon\Carbon;
use DateInterval;
use DateTime;
use DateTimeZone;
use DB;
use App\Services\PushNotificationService;
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
            $ride_date = DateTime::createFromFormat('d/m/Y', $decode->mydate);
            $ride->mydate = $ride_date->format('Y-m-d');
            $ride->mytime = $decode->mytime;
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

                $repeating_dates = $this->recurringDates($ride_date, $repeats_until, $ride->week_days);

                foreach ($repeating_dates as $date) {
                    // Skip if it's the date of the original Ride
                    if ($date == $ride_date) continue;

                    // Creating repeating Ride objects. All fields are the same except for
                    // the date - which will have a new generated date - and a foreign key
                    // to the original Ride (routine_id).
                    $repeating_ride = new Ride();
                    $repeating_ride->myzone = $ride->myzone;
                    $repeating_ride->neighborhood = $ride->neighborhood;
                    $repeating_ride->place = $ride->place;
                    $repeating_ride->route = $ride->route;
                    $repeating_ride->mydate = $date->format('Y-m-d'); // New date
                    $repeating_ride->mytime = $ride->mytime;
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

        $dateTime = Carbon::createFromFormat('d/m/Y H:i:s', $request->input('date') . $request->input('time'));
        $date = $dateTime->format('Y-m-d');
        $timeMin = $dateTime->copy()->subHours(2)->format('H:i:s'); // check for rides a few hours before the time
        $timeMax = $dateTime->copy()->addHours(2)->format('H:i:s'); // check for rides a few hours after the time

        $ridesFound = $request->user->rides()
            ->where(['mydate' => $date, 'going' => $request->input('going')])
            ->whereBetween('mytime', [$timeMin, $timeMax])
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
        DB::transaction(function() use ($request, $rideId) {

            $user = $request->user;

            $matchThese = ['ride_id' => $rideId, 'user_id' => $user->id, 'status' => 'driver'];
            if (RideUser::where($matchThese)->count() < 1) {
                return response()->json(['error'=>'User is not the driver on this ride.'], 403);
            }

            $ride = Ride::find($rideId);
            if ($ride == null) {
                return response()->json(['error'=>'ride not found with id = ' . $rideId], 400);
            }

            RideUser::where('ride_id', $rideId)->delete(); //delete all relationships with this ride first
            $ride->forceDelete();

        });
    }

    public function deleteAllFromUser(Request $request, $userId, $going)
    {
        DB::transaction(function()  use ($request, $going) {

            $user = $request->user;

            $matchThese = ['status' => 'driver', 'going' => $going, 'done' => false];
            $rideIdList = $user->rides()->where($matchThese)->pluck('ride_id')->toArray();

            RideUser::whereIn('ride_id', $rideIdList)->delete(); //delete all relationships with the rides first
            Ride::whereIn('id', $rideIdList)->forceDelete();

        });
    }

    public function deleteAllFromRoutine(Request $request, $routineId)
    {
        DB::transaction(function() use ($request, $routineId) {

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

    public function listAll(Request $request)
    {
        //query the rides
        $limit = 100;
        $timezone = new DateTimeZone('America/Sao_Paulo');
        $minDate = (new DateTime('now', $timezone))->format('Y-m-d H:i:s');
        $maxDate = (new DateTime('tomorrow', $timezone))->format('Y-m-d');

        $rides = DB::select("
        SELECT ride.*, (SELECT user_id FROM ride_user WHERE ride_id = ride.id AND status = 'driver') AS driver_id
        FROM ride_user AS ru
        LEFT JOIN rides AS ride ON ride.id = ru.ride_id
        WHERE (ride.mydate + ride.mytime) >= :minDate
        AND ride.mydate <= :maxDate
        AND ride.done=FALSE
        AND ru.status IN ('pending','accepted','driver')
        GROUP BY ride.id
        HAVING count(ru.user_id)-1 < ride.slots
        ORDER BY (ride.mydate + ride.mytime) ASC
        LIMIT :limit
        ", ['minDate' => $minDate, 'maxDate' => $maxDate, 'limit' => $limit]);

        $results = [];
        foreach($rides as $ride) {
            $driver = User::where('id', $ride->driver_id)->first();

            $ride->driver = $driver;
            unset($ride->driver_id, $ride->created_at, $ride->updated_at, $ride->deleted_at, $ride->done);

            $results[] = $ride;
        }

        return $results;
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
        $matchThese = ['going' => $decode->go, 'mydate' => $decode->date, 'done' => false];

        //query the rides
        if (empty($decode->center)) {
            $rides = Ride::where($matchThese)->whereIn($locationColumn, $locations)->where('mytime', '>=', $decode->time)->get();
        } else {
            $rides = Ride::where($matchThese)->whereIn($locationColumn, $locations)->where('mytime', '>=', $decode->time)->where('hub', 'LIKE', "$decode->center%")->get();
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

        $matchThese = ['ride_id' => $rideID, 'user_id' => $user->id];
        $ride_user = RideUser::where($matchThese)->first();

        //if a relationship already exists, do not create another one
        if ($ride_user != null) {
            return response()->json(['message'=>'Relationship between user and ride already exists as ' . $ride_user->status]);
        }

        //save relationship between ride and user
        $user->rides()->attach($rideID, ['status' => 'pending']);

        //if driver has gcm token, send notification
        $driver = Ride::find($rideID)->users()->where('status', 'driver')->first(); //get ride's driver
        if (!empty($driver->gcm_token)) {
            $data = [
                'message' => "Sua carona recebeu uma solicitação",
                'msgType' => "joinRequest",
                'rideId'  => $rideID
            ];

            $resultGcm = $this->push->sendNotificationToDevices($driver->gcm_token, $data);

            return response()->json(['message'=>'Request sent and driver notified.', 'gcmResponse'=>$resultGcm]);
        } else {
            return response()->json(['message'=>'Request sent but driver did not have GCM token']);
        }
    }

    public function getRequesters($rideId)
    {
        $ride = Ride::find($rideId);
        if ($ride == null) {
            return response()->json(['error'=>'ride not found with id = ' . $rideId], 400);
        }

        return $ride->users()->where('status', 'pending')->get();
    }

    public function answerJoinRequest(Request $request)
    {
        $decode = json_decode($request->getContent());

        //find existing relationship which should be pending
        $matchThese = ['ride_id' => $decode->rideId, 'user_id' => $decode->userId, 'status' => 'pending'];
        $rideUser = RideUser::where($matchThese)->first();
        if ($rideUser == null)
        return response()->json(['error'=>'relationship between ride_id = ' . $decode->rideId . ' and user_id = ' . $decode->userId . ' with status pending not found'], 400);

        $rideUser->status = $decode->accepted ? 'accepted' : 'refused';

        $rideUser->save();

        //if user has gcm token, send notification
        $user = User::find($rideUser->user_id);
        if (!empty($user->gcm_token)) {
            $data = [
                'message' => $decode->accepted ? 'Você foi aceito em uma carona =)' : 'Você foi recusado em uma carona =(',
                'msgType' => $decode->accepted ? 'accepted' : 'refused',
                'rideId'  => $decode->rideId
            ];
            $resultGcm = $this->push->sendNotificationToDevices($user->gcm_token, $data);
            return response()->json(['message'=>'Request answered and user notified.', 'gcmResponse'=>$resultGcm]);
        } else {
            return response()->json(['message'=>'Request answered but user did not have GCM token']);
        }
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

            //get gcm tokens from users accepted on the ride
            $ridersTokens = $ride->users()->where('status', 'accepted')->pluck('gcm_token')->toArray();

            RideUser::where('ride_id', $rideID)->delete();//delete all relationships to this ride
            $ride->delete();

            //send notification to riders on that ride
            $data = [
                'message' => 'Um motorista cancelou uma carona ativa sua',
                'msgType' => 'cancelled',
                'rideId'  => $rideID
            ];

            if (count($ridersTokens) > 1) {
                $resultGcm = $this->push->sendNotificationToDevices($ridersTokens, $data);
                return response()->json(['message'=>'Left ride and users were notified.', 'gcmResponse'=>$resultGcm]);
            }
            if (count($ridersTokens) == 1) {
                $resultGcm = $this->push->sendNotificationToDevices($ridersTokens[0], $data);
                return response()->json(['message'=>'Left ride and users were notified.', 'gcmResponse'=>$resultGcm]);
            }
            //this doesn't handle the case where users' gcm tokens aren't null but are empty (''), they'll still be on the $ridersToken and will receive an error from gcm
            if (count($ridersTokens) == 0) {
                return response()->json(['message'=>'Left ride but no users have a gcm token.']);
            }
        } else {//if user is not the driver, just set relationship as quit
            $rideUser->status = 'quit';
            $rideUser->save();

            $driver = Ride::find($rideID)->users()->where('status', 'driver')->first();
            if (!empty($driver->gcm_token)) { //if user has gcm token, send notification to him
                $data = [
                    'message' => 'Um caronista desistiu de sua carona',
                    'msgType' => 'quitter'
                ];
                $resultGcm = $this->push->sendNotificationToDevices($driver->gcm_token, $data);
                return response()->json(['message'=>'Left ride and users were notified.', 'gcmResponse'=>$resultGcm]);
            } else {
                return response()->json(['message'=>'Left ride but driver did not have gcm token.']);
            }
        }
    }

    public function finishRide(Request $request)
    {
        $user = $request->user;

        $ride = Ride::find($request->rideId);
        if ($ride == null) {
            return response()->json(['error'=>'ride not found with id = ' . $rideId], 400);
        }

        //find existing relationship which should be driver
        $matchThese = ['ride_id' => $ride->id, 'user_id' => $user->id, 'status' => 'driver'];
        $rideUser = RideUser::where($matchThese)->first();
        if ($rideUser == null) {
            return response()->json(['error'=>'user is not the driver of this ride'], 403);
        }

        $ride->done = true;
        $ride->save();

        //get gcm tokens from users accepted on the ride
        $ridersTokens = $ride->users()->where('status', 'accepted')->pluck('gcm_token')->toArray();

        //send notification to riders on that ride
        $data = [
            'message' => 'Um motorista concluiu uma carona ativa sua',
            'msgType' => 'finished',
            'rideId' => $request->rideId
        ];

        if (count($ridersTokens) > 1) {
            $resultGcm = $this->push->sendNotificationToDevices($ridersTokens, $data);
            return response()->json(['message'=>'Ride finished and users were notified.', 'gcmResponse'=>$resultGcm]);
        }
        if (count($ridersTokens) == 1) {
            $resultGcm = $this->push->sendNotificationToDevices($ridersTokens[0], $data);
            return response()->json(['message'=>'Ride finished and users were notified.', 'gcmResponse'=>$resultGcm]);
        }
        //this doesn't handle the case where users' gcm tokens aren't null but are empty (''), they'll still be on the $ridersToken and will receive an error from gcm
        if (count($ridersTokens) == 0) {
            return response()->json(['message'=>'Ride finished but no users have a gcm token.']);
        }
    }

    public function getRidesHistory(Request $request)
    {
        $user = $request->user;
        $rides = $user->rides()->where('done', true)->whereIn('status', ['driver', 'accepted'])->get();

        $resultJson = [];
        foreach($rides as $ride) {
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
        if ($user == null) {
            return response()->json(['error'=>'User not found with id = ' . $userId], 400);
        }

        $offeredCount = $user->rides()->where('done', true)->where('status', 'driver')->count();
        $takenCount = $user->rides()->where('done', true)->where('status', 'accepted')->count();

        return [
            "offeredCount" => $offeredCount,
            "takenCount" => $takenCount
        ];
    }

    public function saveFeedback(Request $request)
    {
        $decode = json_decode($request->getContent());
        $matchThese = ['ride_id' => $decode->rideId, 'user_id' => $decode->userId];
        $ride_user = RideUser::where($matchThese)->first();

        if ($ride_user == null) {
            return response()->json(['error'=>'relationship between user with id ' . $decode->userId . ' and ride with id '. $decode->rideId . ' does not exist or ride does not exist'], 400);
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

    /// Desktop

    public function index()
    {
        return view('rides.index');
    }

    public function indexJson(RankingRequest $request)
    {
        // o RankingRequest está sendo usado para reutilizar código, mas isso tecnicamente não é um ranking
        return Ride::getInPeriodWithUserInfo($request->getDate('start'), $request->getDate('end'));
    }

    public function indexExcel(RankingRequest $request)
    {
        $data = Ride::getInPeriodWithUserInfo($request->getDate('start'), $request->getDate('end'));

        (new ExcelExporter())->exportWithBlade('caronas-dadas', 'rides.excel-export-sheet',
        ['Motorista', 'Curso', 'Data', 'Hora', 'Origem', 'Destino', 'Distancia', 'Distancia Total', 'Total de Caronas', 'Distancia Média'],
        $data->toArray(), $request->get('type', 'xlsx'));
    }

    public function riders($rideId)
    {
        $ride = Ride::find($rideId);

        return $ride->users()->where('status', 'accepted')->get();
    }
}
