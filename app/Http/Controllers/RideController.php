<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Ride;
use App\User;
use App\RideUser;
use App\Http\PostGCM;

use \DateTime;
use \DateInterval;

class RideController extends Controller
{
    public function store(Request $request) {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		if ($user->deleted_at != null) {
			return response()->json(['error'=>'User banned.'], 403);
		}
		
		//create new ride and save it
        $ride = new Ride();
		$ride->myzone = $decode->myzone;
		$ride->neighborhood = $decode->neighborhood;
		$ride->place = $decode->place;
		$ride->route = $decode->route;
        $mydate = DateTime::createFromFormat('d/m/Y', $decode->mydate);
		$ride->mydate = $mydate->format('Y-m-d');
		$ride->mytime = $decode->mytime;
		$ride->slots = $decode->slots;
		$ride->hub = $decode->hub;
		$ride->description = $decode->description;
		$ride->going = $decode->going;
        $repeats_until = DateTime::createFromFormat('d/m/Y', $decode->repeats_until);
		if ($decode->repeats_until != "") {
			$ride->repeats_until = $repeats_until->format('Y-m-d');
		} else {
			$ride->repeats_until = NULL;
		}
		$ride->week_days = $decode->week_days;
		
		$ride->save();

		$rides_created[] = $ride;
		
		//save relationship between ride and user
		$user->rides()->attach($ride->id, ['status' => 'driver']);

		// Check if ride generates a routine and create future events
		if ($decode->week_days != "") {
			$initial_date = $mydate->setTime(0,0,0);
			$repeats_until = $repeats_until->setTime(23,59,59);
			// Convert week days string (e.g. 1,3,5 for mon, wed and fri) to array
			$week_days = explode(',', $ride->week_days);

			// Check if the format is ok
			if (count($week_days) > 7) {
				return response()->json(['error'=>'Field "week_days" expects up to 7 elements.'], 400);
			}
			$week_days = array_unique($week_days); // Remove duplicated days
			foreach ($week_days as $week_day) {
				if ($week_day < 1 || $week_day > 7) {
					return response()->json(['error'=>'Field "week_days" expects elements with range [1,7].'], 400);
				}
			}

			// Calculate the interval between each week day (e.g. If the event starts on mondays
			// and repeats mondays and wednesdays, there is a two day difference from the initial
			// date and 5 days to the next monday. I can then add those days to the initial date
			// until I reach the ending date.)
			$repeating_intervals = [];
			for ($i=0; $i<count($week_days); $i++) {
				$next_week_day = $week_days[($i+1) % count($week_days)]; // Get next element or first if next doesn't exist
				$d = $next_week_day - $week_days[$i]; // Days between this week day and the next occurence
				// If the result is 0 or less, it means the next day is in the following week, so let's add 7 to make it > 1 again.
				if ($d <= 0) {
					$d += 7;
				}

				// TODO: Só pra testar
				if ($d <= 0) {
					return response()->json(['error'=>'Internal error generating routines (negative interval).'], 500);
				}

				$repeating_intervals[$week_days[$i]] = $d;
			}

			// TODO: Só pra testar
			if (count($repeating_intervals) == 0) {
				return response()->json(['error'=>'Internal error generating routines (no repeating patterns).'], 500);
			}

			// Find first occurence of event. If the date for the original event is not one
			// of the week days of the routine, let's find the first one which is.
			$routine_first_date = $initial_date->add(new DateInterval('P1D'));
			$routine_first_date_week_day = $routine_first_date->format('N');
			while (!in_array($routine_first_date_week_day, $week_days)) {
				$routine_first_date = $routine_first_date->add(new DateInterval('P1D'));
				$routine_first_date_week_day = $routine_first_date->format('N');
			}

			// Generate all future events until end date
			$repeating_ride_date = $routine_first_date;
			do {
				if ($repeating_ride_date > $repeats_until) {
					break;
				}
				
				// Creating repeating ride object. All fields are the same except for 
				// the date - which will have a new generated date - and a foreign key
				// to the original ride (routine_id).
				$repeating_ride = new Ride();
				$repeating_ride->myzone = $decode->myzone;
				$repeating_ride->neighborhood = $decode->neighborhood;
				$repeating_ride->place = $decode->place;
				$repeating_ride->route = $decode->route;
				$repeating_ride->mydate = $repeating_ride_date->format('Y-m-d'); // New date
				$repeating_ride->mytime = $decode->mytime;
				$repeating_ride->slots = $decode->slots;
				$repeating_ride->hub = $decode->hub;
				$repeating_ride->description = $decode->description;
				$repeating_ride->going = $decode->going;
				$repeating_ride->week_days = $decode->week_days;
				$repeating_ride->routine_id = $ride->id; // References the original ride which originated this ride
				
				$repeating_ride->save();

				$rides_created[] = $repeating_ride;
				
				// Saving the relationship between ride and user
				$user->rides()->attach($repeating_ride->id, ['status' => 'driver']);

				$repeating_ride_date_week_day = $repeating_ride_date->format('N');
				$repeating_ride_date = $repeating_ride_date->add(new DateInterval('P' . $repeating_intervals[$repeating_ride_date_week_day] .  'D'));
			} while ($repeating_ride_date <= $repeats_until);
		}
				
		return $rides_created;
    }
	
	public function delete($rideId) {
        RideUser::where('ride_id', $rideId)->delete(); //delete all relationships with this ride first
        Ride::destroy($rideId);
    }

    public function listAll(Request $request) {
		$user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		
		//query the rides
		$rides = Ride::where('mydate', '>=', new DateTime('today'))->take(50)->get();
		
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

    public function listFiltered(Request $request) {
        $decode = json_decode($request->getContent());
		$user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		if ($user->deleted_at != null) {
			return response()->json(['error'=>'User banned.'], 403);
		}
		
		//locations will come as a string divided by ", ", explode the string into an array 
		$locations = explode(", ", $decode->location);
		
		//location can be zones or neighborhoods, check if first array position is a zone or a neighborhood
		if ($locations[0] == "Centro" || $locations[0] == "Zona Sul" || $locations[0] == "Zona Oeste" || $locations[0] == "Zona Norte" || $locations[0] == "Baixada" || $locations[0] == "Grande Niterói")
				$locationColumn = 'myzone';//if location is filtered by zone, query by 'myzone' column
		else
				$locationColumn = 'neighborhood';//if location is filtered by neighborhood, query by 'neighborhood' column
		
		$matchThese = ['going' => $decode->go, 'mydate' => $decode->date, 'done' => false];
		
		//query the rides
		if (empty($decode->center)) {
			$rides = Ride::where($matchThese)->whereIn($locationColumn, $locations)->where('mytime', '>=', $decode->time)->whereNull('deleted_at')->get();
		} else {
			$rides = Ride::where($matchThese)->whereIn($locationColumn, $locations)->where('mytime', '>=', $decode->time)->whereNull('deleted_at')->where('hub', 'LIKE', "$decode->center%")->get();
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
	
	public function requestJoin(Request $request) {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		if ($user->deleted_at != null) {
			return response()->json(['error'=>'User banned.'], 403);
		}
		
		$matchThese = ['ride_id' => $decode->rideId, 'user_id' => $user->id];
        $ride_user = RideUser::where($matchThese)->first();
		
		//if a relationship already exists, do not create another one
		if ($ride_user != null) {
			return response()->json(['message'=>'Relationship between user and ride already exists as ' . $ride_user->status]);
		}
		
		//save relationship between ride and user
		$user->rides()->attach($decode->rideId, ['status' => 'pending']);
		
		//send notification
		$driver = Ride::find($decode->rideId)->users()->where('status', 'driver')->first(); //get ride's driver
		if (!empty($driver->gcm_token)) { //if driver has gcm token, send notification to him
			$postGcm = new PostGCM();
			$data = array( 	'message' 	=> "Sua carona recebeu uma solicitação",
							'msgType' 	=> "joinRequest"
						 );
			$body = array(	'to' 		=> $driver->gcm_token,
							'data' 		=> $data
						 );

			$resultGcm = $postGcm->doPost($body);

			return response()->json(['message'=>'Request sent and driver notified.', 'gcmResponse'=>$resultGcm]);
		} else {
			return response()->json(['message'=>'Request sent but driver did not have GCM token']);
		}
	}
	
	public function getRequesters($rideId) {
        $ride = Ride::find($rideId);
		if ($ride == null) {
			return response()->json(['error'=>'ride not found with id = ' . $rideId], 400);
		}
		
		return $ride->users()->where('status', 'pending')->get();
    }
	
	public function answerJoinRequest(Request $request) {
        $decode = json_decode($request->getContent());
		
		//find existing relationship which should be pending
		$matchThese = ['ride_id' => $decode->rideId, 'user_id' => $decode->userId, 'status' => 'pending'];
        $rideUser = RideUser::where($matchThese)->first();
		if ($rideUser == null)
			return response()->json(['error'=>'relationship between ride_id = ' . $decode->rideId . ' and user_id = ' . $decode->userId . ' with status pending not found'], 400);
		
		$rideUser->status = $decode->accepted ? 'accepted' : 'refused';
		
		$rideUser->save();
		
		//send notification
		$user = User::find($rideUser->user_id);
		if (!empty($user->gcm_token)) { //if user has gcm token, send notification to him
			$postGcm = new PostGCM();
			$data = array( 	'message' 	=> $decode->accepted ? 'Você foi aceito em uma carona =)' : 'Você foi recusado em uma carona =(',
									'msgType' 	=> $decode->accepted ? 'accepted' : 'refused',
									'rideId'	 	=> $decode->rideId
									);
			$body = array(	'to' 			=> $user->gcm_token,
									'data' 		=> $data);

			$resultGcm = $postGcm->doPost($body);
			return response()->json(['message'=>'Request answered and user notified.', 'gcmResponse'=>$resultGcm]);
		} else {
			return response()->json(['message'=>'Request answered but user did not have GCM token']);
		}
    }
	
	public function getMyActiveRides(Request $request) {
        $user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		if ($user->deleted_at != null) {
			return response()->json(['error'=>'User banned.'], 403);
		}
		
		//active rides have 'driver' or 'accepted' status
		$rides = $user->rides()->whereIn('status', ['driver', 'accepted'])->whereNull('deleted_at')->where('done', false)->get();
		
		$resultArray = array();
		foreach($rides as $ride) {
			$resultRide = $ride;

			$riders = $ride->users()->whereIn('status', ['driver', 'accepted'])->get();
			if (count($riders) == 1)//if count == 1 driver is the only one on the ride, therefore ride is not active
				continue;
			
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
	
	public function leaveRide(Request $request) {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		
		$matchThese = ['ride_id' => $decode->rideId, 'user_id' => $user->id];
        $rideUser = RideUser::where($matchThese)->first();//get relationship
		if ($rideUser->status == 'driver') {//if user is the driver the ride needs to be deleted
			$ride = Ride::find($decode->rideId);
			
			//get gcm tokens from users accepted on the ride
			$ridersTokens = $ride->users()->where('status', 'accepted')->lists('gcm_token');
			
			RideUser::where('ride_id', $decode->rideId)->delete();//delete all relationships to this ride
			$ride->delete();
			
			//send notification to riders on that ride
			$postGcm = new PostGCM();
			$data = array( 	'message' 	=> 'Um motorista cancelou uma carona ativa sua',
									'msgType' 	=> "cancelled",
									'rideId'	 	=> $decode->rideId
									);
			
			if (count($ridersTokens) > 1) {
				$body = array(	'registration_ids' 	=> $ridersTokens,
										'data' 				=> $data);

				$resultGcm = $postGcm->doPost($body);
				return response()->json(['message'=>'Left ride and users were notified.', 'gcmResponse'=>$resultGcm]);
			}
			if (count($ridersTokens) == 1) {
				$body = array(	'to' 		=> $ridersTokens[0],
										'data' 	=> $data);

				$resultGcm = $postGcm->doPost($body);
				return response()->json(['message'=>'Left ride and users were notified.', 'gcmResponse'=>$resultGcm]);
			}
			//this doesn't handle the case where users' gcm tokens aren't null but are empty (''), they'll still be on the $ridersToken and will receive an error from gcm
			if (count($ridersTokens) == 0) {
				return response()->json(['message'=>'Left ride but no users have a gcm token.']);
			}
		} else {//if user is not the driver, just set relationship as quit
			$rideUser->status = 'quit';
			$rideUser->save();
			
			$driver = Ride::find($decode->rideId)->users()->where('status', 'driver')->first();
			if (!empty($driver->gcm_token)) { //if user has gcm token, send notification to him
				$postGcm = new PostGCM();
				$data = array( 	'message' 	=> 'Um caronista desistiu de sua carona',
										'msgType' 	=> "quitter"
										);
				$body = array(	'to' 			=> $driver->gcm_token,
										'data' 		=> $data);
				$resultGcm = $postGcm->doPost($body);
				return response()->json(['message'=>'Left ride and users were notified.', 'gcmResponse'=>$resultGcm]);
			} else {
				return response()->json(['message'=>'Left ride but driver did not have gcm token.']);
			}
		}
	}

	public function finishRide(Request $request) {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		
		$ride = Ride::find($decode->rideId);
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
		$ridersTokens = $ride->users()->where('status', 'accepted')->lists('gcm_token');
		
		//send notification to riders on that ride
		$postGcm = new PostGCM();
		$data = array( 	'message' 	=> 'Um motorista concluiu uma carona ativa sua',
								'msgType' 	=> "finished",
								'rideId'	 	=> $decode->rideId
								);
		
		if (count($ridersTokens) > 1) {
			$body = array(	'registration_ids' 	=> $ridersTokens,
									'data' 				=> $data);

			$resultGcm = $postGcm->doPost($body);
			return response()->json(['message'=>'Ride finished and users were notified.', 'gcmResponse'=>$resultGcm]);
		}
		if (count($ridersTokens) == 1) {
			$body = array(	'to' 		=> $ridersTokens[0],
									'data' 	=> $data);

			$resultGcm = $postGcm->doPost($body);
			return response()->json(['message'=>'Ride finished and users were notified.', 'gcmResponse'=>$resultGcm]);
		}
		//this doesn't handle the case where users' gcm tokens aren't null but are empty (''), they'll still be on the $ridersToken and will receive an error from gcm
		if (count($ridersTokens) == 0) {
			return response()->json(['message'=>'Ride finished but no users have a gcm token.']);
		}
	}

	public function getRidesHistory(Request $request) {
        $user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		if ($user->deleted_at != null) {
			return response()->json(['error'=>'User banned.'], 403);
		}
		
		$rides = $user->rides()->where('done', true)->whereIn('status', ['driver', 'accepted'])->get();
		
		$resultJson = array();
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
	
	public function getRidesHistoryCount($userId) {
        $user = User::find($userId);
		if ($user == null) {
			return response()->json(['error'=>'User not found with id = ' . $userId], 400);
		}
		if ($user->deleted_at != null) {
			return response()->json(['error'=>'User banned.'], 403);
		}
		
		$offeredCount = $user->rides()->where('done', true)->where('status', 'driver')->count();
		$takenCount = $user->rides()->where('done', true)->where('status', 'accepted')->count();
		
		return array("offeredCount" => $offeredCount, "takenCount" => $takenCount);
	}
	
	public function saveFeedback(Request $request) {
		$decode = json_decode($request->getContent());
        $matchThese = ['ride_id' => $decode->rideId, 'user_id' => $decode->userId];
        $ride_user = RideUser::where($matchThese)->first();
		
		if ($ride_user == null) {
			return response()->json(['error'=>'relationship between user with id ' . $decode->userId . ' and ride with id '. $decode->rideId . ' does not exist or ride does not exist'], 400);
		}
		
		$ride_user->feedback = $request->feedback;
		$ride_user->save();
	}
}
