<?php

namespace Caronae\Http\Middleware;

use Closure;
use Caronae\Models\Institution;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Support\Facades\Auth;

class ApiAuthenticateInstitution extends AuthenticateWithBasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = NULL)
    {
        if (($institution = Institution::where([
            'id'=> $request->getUser(),
            'password' => $request->getPassword()
            ])->first()) == NULL) {
            abort(403);
        }

        $this->institution = $institution;

        return $next($request);
    }

}
