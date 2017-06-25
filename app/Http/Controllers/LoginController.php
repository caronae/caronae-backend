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
        if (!JWTAuth::getToken() && !$request->has('institution')) {
            $institutions = Institution::all();
            return view('login.institutions', [ 'institutions' => $institutions ]);
        }

        try {
            if (!$user = JWTAuth::authenticate()) {
                return $this->error('User not found', 404);
            }
        } catch (JWTException $e) {
            if ($request->has('institution')) {
                $institution = Institution::findOrFail($request->input('institution'));
                return redirect($institution->authentication_url);
            }

            return $this->error('Invalid token', $e->getStatusCode());
        }

        return view('login.token', [ 'user' => $user ]);
    }
}
