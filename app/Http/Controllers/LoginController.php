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
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (JWTException $e) {
            $institution = Institution::find($request->institution);
            return redirect($institution->authentication_url);
        }

        return $user;
    }
}
