<?php

namespace BildVitta\Hub\Middleware;

use BildVitta\Hub\Exceptions\AuthenticationException;
use Closure;
use Illuminate\Http\Request;

/**
 * Class AuthAttemptMiddleware.
 *
 * @package BildVitta\Hub\Middleware
 */
class AuthenticateHubMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (is_null($token)) {
            throw new AuthenticationException(__('Bearer token é obrigatório.'));
        }

        $user = \BildVitta\Hub\Facades\Hub::setToken($token)->users()->me();
        dd('$me', $user);

        #TODO: criar user se nao existir e persistir no banco um de-para

//        auth()->loginUsingId($user->id);

        return $next($request);
    }
}