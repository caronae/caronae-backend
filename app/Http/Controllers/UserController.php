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
        $user->neighborhood = $decode->neighborhood;
        $user->phone_number = $decode->phoneNumber;
        $user->email = $decode->email;
        $user->car_owner = $decode->car_owner;
        $user->car_model = $decode->car_model;
        $user->car_color = $decode->car_color;
        $user->car_plate = $decode->car_plate;
        $user->token = $decode->token;


        $user->save();

        //return var_dump($user);
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
        $decode = json_decode($request->getContent());

        //todo: verificar em um middleware
        $user = User::where('token', $decode->token)->first();

        $user->name = $decode->name;
        $user->profile = $decode->profile;
        $user->course = $decode->course;
        $user->neighborhood = $decode->neighborhood;
        $user->phone_number = $decode->phone_number;
        $user->email = $decode->email;
        $user->car_owner = $decode->car_owner;
        $user->car_model = $decode->car_model;
        $user->car_color = $decode->car_color;
        $user->car_plate = $decode->car_plate;
        $user->token = $decode->token;


        $user->save();
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
