<?php

namespace Caronae\Http\Middleware;

use Caronae\Models\Institution;
use Closure;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Log;

class ApiAuthenticateInstitution extends AuthenticateWithBasicAuth
{
    public function handle($request, Closure $next, $guard = null, $field = null)
    {
        if (($institution = Institution::where([
            'id' => $request->getUser(),
            'password' => $request->getPassword()
            ])->first()) == NULL) {
            Log::warning('Autenticação da instituição falhou', [ 'id' => $request->getUser(), 'password' => $request->getPassword() ]);
            return response()->json(['error' => 'Institution not authorized.'], 401);
        }

        $request->merge([
            'institution' => $institution
        ]);

        return $next($request);
    }

}
