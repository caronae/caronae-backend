<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

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
        $user = new User();

        $user->name = $decode->name;
        $user->profile = $decode->profile;
        $user->course = $decode->course;
        $user->unit = $decode->unit;
        $user->zone = $decode->zone;
        $user->neighborhood = $decode->neighborhood;
        $user->phoneNumber = $decode->phoneNumber;
        $user->email = $decode->phoneNumber;
        $user->carOwner = $decode->carOwner;
        $user->carModel = $decode->carModel;
        $user->carColor = $decode->carColor;
        $user->carPlate = $decode->carPlate;


        //$user->save();

        return var_dump($user);
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

    // POST user/{id}/edit
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id, Request $request)
    {
        $user = User::where('id', $id)->first();

        //todo: leonardo prefere fazer pelo header ou json decode como foi a outra request?
        $user->profile = $request->header('profile');

        /*
        $user->name = $request->header('name');
        $user->profile = $request->header('profile');
        $user->course = $request->header('course');
        $user->unit = $request->header('unit');
        $user->zone = $request->header('zone');
        $user->neighborhood = $request->header('neighborhood');
        $user->phoneNumber = $request->header('phoneNumber');
        $user->email = $request->header('email');
        $user->carOwner = $request->header('carOwner');
        $user->carModel = $request->header('carModel');
        $user->carColor = $request->header('carColor');
        $user->carPlate = $request->header('carPlate');
        */

        $user->save();

        //para debug
        return var_dump($user);

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
