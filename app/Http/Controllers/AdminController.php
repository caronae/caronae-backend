<?php

namespace App\Http\Controllers;

use App\Admin;
use App\Http\Requests;
use App\Http\Requests\AdminEditRequest;

class AdminController extends Controller
{

    public function getIndex()
    {
        return view('admin.index');
    }

    public function getEdit(){
        $data['admin'] = Admin::first();
        return view('admin.edit')->with($data);
    }

    public function postEdit(AdminEditRequest $request)
    {
        $admin = Admin::first();

        $admin->fill($request->all());

        if($request->get('password')){
            $admin->password = bcrypt($request->get('password'));
        }

        $admin->save();

        return back()->with('message', 'Administrador editado com sucesso.');

    }
}