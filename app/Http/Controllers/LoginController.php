<?php

namespace Caronae\Http\Controllers;

use Caronae\Models\Institution;
use Cookie;
use Illuminate\Http\Request;
use JWTAuth;
use Log;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('error')) {
            $error = $request->input('error');
            Log::info('Login: instituição não autorizou login.', [ 'error' => $error, 'referer' => $request->headers->get('referer') ]);
            return response()->view('login.error', [ 'error' => $error ], 401);
        }

        if (!$request->has('token') && !$request->has('error')) {
            $institutions = Institution::all();

            if (count($institutions) == 1) {
                $institution = $institutions->first();
                return redirect($institution->authentication_url);
            }

            return view('login.institutions', [ 'institutions' => $institutions ]);
        }

        try {
            $user = $this->authenticateUser($request);
            Log::info('Login: usuário autenticado.', [ 'id' => $user->id, 'referer' => $request->headers->get('referer') ]);
        } catch (JWTException $e) {
            Log::warning('Login: erro autenticando token.', [ 'error' => $e->getMessage(), 'token' => $request->input('token') ]);
            return response()->view('login.error', [ 'error' => 'Token inválido.' ], 401);
        }

        return view('login.token', [
            'user' => $user,
            'token' => $request->input('token'),
            'displayTermsOfUse' => !$this->hasAcceptedTermsOfUse($request)
        ]);
    }

    public function refreshToken(Request $request)
    {
        try {
            $user = $this->authenticateUser($request);
        } catch (JWTException $e) {
            Log::warning('refreshToken: erro autenticando token.', [ 'error' => $e->getMessage(), 'token' => $request->input('token') ]);
            return response()->view('login.error', [ 'error' => 'Token inválido.' ], 401);
        }

        $user->generateToken();
        $user->save();

        Log::info('refreshToken: nova chave gerada.', [ 'id' => $user->id, 'referer' => $request->headers->get('referer') ]);
        return redirect(route('chave', $request->only(['token'])));
    }

    private function authenticateUser(Request $request)
    {
        JWTAuth::setToken($request->input('token'));
        $user = JWTAuth::authenticate();
        if (!$user) throw new JWTException('User not found', 401);
        return $user;
    }

    private function hasAcceptedTermsOfUse(Request $request)
    {
        if ($request->has('acceptedTermsOfUse')) {
            Cookie::queue('acceptedTermsOfUse', true);
            return true;
        }

        return $request->cookie('acceptedTermsOfUse', false);
    }
}
