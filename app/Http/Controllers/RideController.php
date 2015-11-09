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
			return 'usuário não encontrado com esse token';
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
			$ride->repeats_until = "";
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
	
    public function listFiltered(Request $request) {
        $decode = json_decode($request->getContent());
		
		//locations will come as a string divided by ", ", explode the string into an array 
		$locations = explode(", ", $decode->location);
		
		//location can be zones or neighborhoods, check if first array position is a zone or a neighborhood
		if ($locations[0] == "Centro" || $locations[0] == "Zona Sul" || $locations[0] == "Zona Oeste" || $locations[0] == "Zona Norte" || $locations[0] == "Baixada" || $locations[0] == "Grande Niterói")
				$locationColumn = 'myzone';//if location is filtered by zone, query by 'myzone' column
		else
				$locationColumn = 'neighborhood';//if location is filtered by neighborhood, query by 'neighborhood' column
		
		$matchThese = ['going' => $decode->go, 'mydate' => $decode->date];
		//query the rides
		$rides = Ride::where($matchThese)->whereIn($locationColumn, $locations)->get();
		
		if ($rides->count() > 0) {
			$resultJson = '[';
			
			foreach ($rides as $ride) {
				//check if ride is full
				if ($ride->users()->where('status', 'pending')->orWhere('status', 'accepted')->count() < $ride->slots) {
					//gets the driver
					$user = $ride->users()->where('status', 'driver')->first();
					
					//make array with driver's info and ride's info
					$arr = array('driverId' => $user->id,
										'driverName' => $user->name, 
										'course' => $user->course, 
										'neighborhood' => $ride->neighborhood, 
										'zone' => $ride->myzone, 
										'place' => $ride->place, 
										'route' => $ride->route, 
										'time' => $ride->mytime, 
										'date' => $ride->mydate, 
										'slots' => $ride->slots, 
										'hub' => $ride->hub, 
										'going' => $ride->going, 
										'rideId' => $ride->id);
					
					$resultJson .= json_encode($arr) . ',';
				}
			}
			
			//remove last ',' from json
			if (strlen($resultJson) > 1) {
				$resultJson = substr($resultJson, 0, -1);  
				$resultJson .= ']';
				
				return $resultJson;
			}
		}
    }
	
	public function requestJoin(Request $request) {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return 'usuário não encontrado com esse token';
		}
		
		$matchThese = ['ride_id' => $decode->rideId, 'user_id' => $user->id];
        $ride_user = RideUser::where($matchThese)->first();
		
		//if a relationship already exists, do not create another one
		if ($ride_user != null)
			return 'relationship between user and ride already exists as ' . $ride_user->status;
		
		//save relationship between ride and user
		$user->rides()->attach($decode->rideId, ['status' => 'pending']);
		
		//send notification
		$driver = Ride::find($decode->rideId)->users()->where('status', 'driver')->first(); //get ride's driver
		if (!empty($driver->gcm_token)) { //if driver has gcm token, send notification to him
			$postGcm = new PostGCM();
			return $postGcm->postToOne("Sua carona recebeu uma solicitação", $driver->gcm_token);
		} else {
			return 'request sent but driver did not have gcm token';
		}
	}
	
	public function getRequesters(Request $request) {
        $decode = json_decode($request->getContent());
		
        $ride = Ride::find($decode->rideId);
		if ($ride == null) {
			return 'ride not found with id = ' . $decode->rideId;
		}
		
		return $ride->users()->where('status', 'pending')->get();
    }
	
	public function getMyActiveRides(Request $request) {
        $user = User::where('token', $request->header('token'))->first();
		
		$rides = $user->rides;
		$resultArray = array();
		foreach($rides as $ride) {
			if ($ride->pivot->status == 'driver' || $ride->pivot->status == 'accepted') {
					$users = $ride->users;
					
					$users2 = array();
					foreach($users as $user2) {
						if ($user2->pivot->status == 'driver') {
							array_unshift($users2, $user2);
						}
						if ($user2->pivot->status == 'accepted') {
							array_push($users2, $user2);
						}
					}
					
					if(count($users2) > 1) {
						array_push($resultArray, array("ride" => $ride, "users" => $users2));
					}
			}
		}
		
		return $resultArray;
	}
	
	public function leaveRide(Request $request) {
		
        $user = User::where('token', $request->header('token'))->first();
        $decode = json_decode($request->getContent());
		
		$matchThese = ['ride_id' => $decode->rideId, 'user_id' => $user->id];
        $rideUser = RideUser::where($matchThese)->first();
		if ($rideUser->status == 'driver') {
			$ride = Ride::find($decode->rideId);
			
			$riders = $ride->users()->where('status', 'accepted')->get();
			
			foreach($riders as $rider) {
				$ridersTokens[] = $rider->gcm_token;
			}
			
			RideUser::where('ride_id', $decode->rideId)->delete();
			$ride->delete();
			
			$postGcm = new PostGCM();
			if (count($ridersTokens) > 1) {
				return $postGcm->postToMany("Um motorista cancelou uma carona ativa sua", $ridersTokens);
			} else {
				return $postGcm->postToOne("Um motorista cancelou uma carona ativa sua", reset($ridersTokens));
			}
			
		} else {
			$rideUser->status = 'quit';
			$rideUser->save();
			
			//send notification to driver
			$matchThese = ['ride_id' => $decode->rideId, 'status' => 'driver'];
			$rideUser = RideUser::where($matchThese)->first();
		
			$user = User::find($rideUser->user_id);
			$postGcm = new PostGCM();
			return $postGcm->postToOne("Um caronista desistiu de sua carona", $user->gcm_token);
		}
}
	
	public function delete(Request $request) {
        $decode = json_decode($request->getContent());
        RideUser::where('ride_id', $decode->rideId)->delete();
        Ride::find($decode->rideId)->delete();
    }
	
	public function answerJoinRequest(Request $request) {
        $decode = json_decode($request->getContent());
		
		$matchThese = ['ride_id' => $decode->rideId, 'user_id' => $decode->userId, 'status' => 'pending'];
        $rideUser = RideUser::where($matchThese)->first();
		$rideUser->status = $decode->accepted ? 'accepted' : 'refused';
		
		$rideUser->save();
		
		//send notification		
		$user = User::find($rideUser->user_id);
		$postGcm = new PostGCM();
		$message = $decode->accepted ? 'Você foi aceito em uma carona =)' : 'Você foi recusado em uma carona =(';
		return $postGcm->postToOne($message, $user->gcm_token);
    }
}
