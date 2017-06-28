<?php

namespace Caronae\Http\Controllers;

use Caronae\Http\Requests\LoginRequest;
use Caronae\Models\Institution;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    public function index(LoginRequest $request)
    {
        if ($request->has('error')) {
            return view('login.error', [ 'error' => $request->input('error') ]);
        }

        if (!$request->hasSelectedInstitution()) {
            $institutions = Institution::all();
            return view('login.institutions', [ 'institutions' => $institutions ]);
        }

        try {
            $user = $request->authenticateUser();
        } catch (JWTException $e) {
            return $this->error('Invalid token', $e->getStatusCode());
        }

        return view('login.token', [ 'user' => $user ]);
    }
}
