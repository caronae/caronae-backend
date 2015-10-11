<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Ride;
use App\User;
use App\RideUser;

class RideController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
	}
    
	public function requestJoin(Request $request) {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
		
		$ride_user = new RideUser();
        $ride_user->user_id = $user->id;
        $ride_user->ride_id = $decode->rideId;
        $ride_user->status = 1;
        
		$ride_user->save();
	}
	
    public function listAll(Request $request)
    {
        $rides = Ride::all();
		
		$resultJson = '[';
		
		foreach ($rides as $ride) {
			$user = $ride->users()->where('status', 0)->first();
			
			$arr = array('driverName' => $user->name, 
								'course' => $user->course, 
								'neighborhood' => $ride->neighborhood, 
								'place' => $ride->place, 
								'route' => $ride->route, 
								'time' => $ride->mytime, 
								'slots' => $ride->slots, 
								'hub' => $ride->hub, 
								'going' => $ride->going, 
								'rideId' => $ride->id, 
								'driverId' => $user->id);
			
			$resultJson .= json_encode($arr) . ',';
		}
		
		$resultJson = substr($resultJson, 0, -1);  
		$resultJson .= ']';
		
		return $resultJson;
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
        $decode = json_decode($request->getContent());
		
        $ride = new Ride();
		$ride->myzone = $decode->myzone;
		$ride->neighborhood = $decode->neighborhood;
		$ride->place = $decode->place;
		$ride->route = $decode->route;
		$ride->mydate = $decode->mydate;
		$ride->mytime = $decode->mytime;
		$ride->slots = $decode->slots;
		$ride->hub = $decode->hub;
		$ride->description = $decode->description;
		$ride->going = $decode->going;
		
		$ride->save();
		
        $user = User::where('token', $request->header('token'))->first();
		
        $ride_user = new RideUser();
        $ride_user->user_id = $user->id;
        $ride_user->ride_id = $ride->id;
        $ride_user->status = 0;
        
		$ride_user->save();
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
