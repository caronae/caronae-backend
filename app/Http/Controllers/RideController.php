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
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
		//
	}

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $user = User::where('token', $request->header('token'))->first();
		
        $decode = json_decode($request->getContent());
		
        $mydate = DateTime::createFromFormat('d/m/Y', $decode->mydate);
        $repeats_until = DateTime::createFromFormat('d/m/Y', $decode->repeats_until);
		
        $ride = new Ride();
		$ride->myzone = $decode->myzone;
		$ride->neighborhood = $decode->neighborhood;
		$ride->place = $decode->place;
		$ride->route = $decode->route;
		$ride->mydate = $mydate->format('Y-m-d');
		$ride->mytime = $decode->mytime;
		$ride->slots = $decode->slots;
		$ride->hub = $decode->hub;
		$ride->description = $decode->description;
		$ride->going = $decode->going;
		if ($decode->repeats_until != "") {
			$ride->repeats_until = $repeats_until->format('Y-m-d');
		} else {
			$ride->repeats_until = "";
		}
		$ride->week_days = $decode->week_days;
		$ride->save();

		$rides_created[] = $ride;
		
        $ride_user = new RideUser();
        $ride_user->user_id = $user->id;
        $ride_user->ride_id = $ride->id;
        $ride_user->status = 'driver';
        
		$ride_user->save();

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
				$ride_user = new RideUser();
				$ride_user->user_id = $user->id;
				$ride_user->ride_id = $repeating_ride->id;
				$ride_user->status = 'driver';
				
				$ride_user->save();

				$repeating_ride_date_week_day = $repeating_ride_date->format('N');
				$repeating_ride_date = $repeating_ride_date->add(new DateInterval('P' . $repeating_intervals[$repeating_ride_date_week_day] .  'D'));
			} while ($repeating_ride_date <= $repeats_until);
		}
				
		return $rides_created;
    }
	
	public function requestJoin(Request $request) {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
		
		$matchThese = ['ride_id' => $decode->rideId, 'user_id' => $user->id];
        $ride_user = RideUser::where($matchThese)->first();
		
		/*if ($ride_user != null)
			return;*/
		
        $ride_user = new RideUser();
        $ride_user->user_id = $user->id;
        $ride_user->ride_id = $decode->rideId;
		$ride_user->status = 'pending';
        
		$ride_user->save();
		
		//send notification
		$matchThese = ['ride_id' => $decode->rideId, 'status' => 'driver'];
        $ride_user = RideUser::where($matchThese)->first();
		
        $user = User::find($ride_user->user_id);
		$postGcm = new PostGCM();
		return $postGcm->post("Sua carona recebeu uma solicitação", $user->gcm_token);
	}
	
    public function listFiltered(Request $request)
    {
        $decode = json_decode($request->getContent());
		
		$matchThese = ['going' => $decode->go, 'mydate' => $decode->date];
		
		$locations = explode(", ", $decode->location);
		
		if ($locations[0] == "Centro" || $locations[0] == "Zona Sul" || $locations[0] == "Zona Oeste" || $locations[0] == "Zona Norte" || $locations[0] == "Baixada" || $locations[0] == "Grande Niterói")
				$locationColumn = 'myzone';
		else
				$locationColumn = 'neighborhood';
			
		$rides = Ride::where($matchThese)->whereIn($locationColumn, $locations)->get();
		
		if ($rides->count() > 0) {
			$resultJson = '[';
			
			foreach ($rides as $ride) {
				$matchThese2 = ['ride_id' => $ride->id, 'status' => 'pending'];
				$matchThese3 = ['ride_id' => $ride->id, 'status' => 'accepted'];
				if (RideUser::where($matchThese2)->orWhere($matchThese3)->count() < $ride->slots) {
					$user = $ride->users()->where('status', 'driver')->first();
					
					$arr = array('driverName' => $user->name, 
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
										'rideId' => $ride->id, 
										'driverId' => $user->id);
					
					$resultJson .= json_encode($arr) . ',';
				}
			}
			
			if (strlen($resultJson) > 1) {
				$resultJson = substr($resultJson, 0, -1);  
				$resultJson .= ']';
				
				return $resultJson;
			}
		}
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
							//$users3[] = $user2;
							array_unshift($users2, $user2);
						}
						if ($user2->pivot->status == 'accepted') {
							//$users3[] = $user2;
							array_push($users2, $user2);
						}
					}
					
					if(count($users2) > 1) {
						//$resultArray[$ride] = $users2;
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
			RideUser::where('ride_id', $decode->rideId)->delete();
			Ride::find($decode->rideId)->delete();
		} else {
			$rideUser->status = 'quit';
			$rideUser->save();
		}
}
	
	public function delete(Request $request)
    {
        $decode = json_decode($request->getContent());
        RideUser::where('ride_id', $decode->rideId)->delete();
        Ride::find($decode->rideId)->delete();
    }
	
	public function getRequesters(Request $request)
    {
        $decode = json_decode($request->getContent());
        $ride = Ride::find($decode->rideId);
        $users = $ride->users;
		
		$requesters = array();
		foreach($users as $user) {
			if ($user->pivot->status == 'pending') {
				array_push($requesters, $user);
			}
		}

        return $requesters;
    }
	
	public function answerJoinRequest(Request $request)
    {
        $decode = json_decode($request->getContent());
		
		$matchThese = ['ride_id' => $decode->rideId, 'user_id' => $decode->userId, 'status' => 'pending'];
        $rideUser = RideUser::where($matchThese)->first();
		$rideUser->status = $decode->accepted ? 'accepted' : 'refused';
		
		$rideUser->save();
		
		//send notification		
		$user = User::find($rideUser->user_id);
		$postGcm = new PostGCM();
		$message = $decode->accepted ? 'Você foi aceito em uma carona =)' : 'Você foi recusado em uma carona =(';
		return $postGcm->post($message, $user->gcm_token);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }


}
