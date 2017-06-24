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
        if (!JWTAuth::getToken() && !$request->institution) {
            $institutions = Institution::all();
            return view('chave.institutions', [ 'institutions' => $institutions ]);
        }

        try {
            if (!$user = JWTAuth::authenticate()) {
                return $this->error('User not found', 404);
            }
        } catch (JWTException $e) {
            if ($request->institution) {
                $institution = Institution::find($request->institution);
                return redirect($institution->authentication_url);
            }

            return $this->error('Invalid token', $e->getStatusCode());
        }

        return view('chave.chave', [ 'user' => $user ]);
    }
}
