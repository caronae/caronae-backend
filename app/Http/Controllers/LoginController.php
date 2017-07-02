<?php

namespace Caronae\Http\Controllers;

use Caronae\Models\Institution;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('error')) {
            $error = $request->input('error');
            return response()->view('login.error', [ 'error' => $error ], 401);
        }

        if (!$request->has('token') && !$request->has('error')) {
            $institutions = Institution::all();
            return view('login.institutions', [ 'institutions' => $institutions ]);
        }

        try {
            $user = $this->authenticateUser();
        } catch (JWTException $e) {
            return response()->view('login.error', [ 'error' => 'Token inválido.' ], 401);
        }

        return view('login.token', [ 'user' => $user ]);
    }

    private function authenticateUser()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) throw new JWTException('User not found', 401);
        return $user;
    }
}
