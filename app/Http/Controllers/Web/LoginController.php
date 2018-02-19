<?php

namespace Caronae\Http\Controllers\Web;

use Caronae\Http\Controllers\BaseController;
use Caronae\Models\Institution;
use Cookie;
use Illuminate\Http\Request;
use JWTAuth;
use Log;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends BaseController
{
    const SESSION_LOGIN_TYPE = 'login_type';

    public function index(Request $request)
    {
        $this->rememberLoginType($request);

        if ($request->filled('error')) {
            $error = $request->input('error');
            Log::info('Login: instituição não autorizou login.', [ 'error' => $error, 'referer' => $request->headers->get('referer') ]);
            return response()->view('login.error', [ 'error' => $error ], 401);
        }

        if (!$request->filled('token') && !$request->filled('error')) {
            $institutions = Institution::all();

            if (count($institutions) == 1) {
                $institution = $institutions->first();
                return redirect($institution->authentication_url);
            }

            return view('login.institutions', [ 'institutions' => $institutions ]);
        }

        try {
            $user = $this->authenticateUser($request);
            Log::info('Login: usuário autenticado.', [
                'id' => $user->id,
                self::SESSION_LOGIN_TYPE => $this->getLoginType($request),
                'referer' => $request->headers->get('referer'),
            ]);
        } catch (JWTException $e) {
            Log::warning('Login: erro autenticando token.', [ 'error' => $e->getMessage(), 'token' => $request->input('token') ]);
            return response()->view('login.error', [ 'error' => 'Token inválido.' ], 401);
        }

        if ($this->isAppLogin($request)) {
            return redirect()->away('caronae://login?id=' . $user->id . '&id_ufrj=' . $user->id_ufrj . '&token=' . $user->token);
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

    private function rememberLoginType(Request $request)
    {
        $session = $request->session();
        if ($request->filled('type')) {
            $login_type = $request->input('type');
            $session->put(self::SESSION_LOGIN_TYPE, $login_type);

            return;
        }

        if (!$session->has(self::SESSION_LOGIN_TYPE)) {
            $session->put(self::SESSION_LOGIN_TYPE, 'web');
        }
    }

    private function getLoginType(Request $request)
    {
        return $request->session()->get(self::SESSION_LOGIN_TYPE, 'web');
    }

    private function isAppLogin(Request $request)
    {
        return $this->getLoginType($request) == 'app';
    }
}
