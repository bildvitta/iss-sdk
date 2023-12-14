<?php

namespace BildVitta\Hub\Middleware\Helpers;

use BildVitta\Hub\Exceptions\AuthenticationException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;

abstract class AuthenticateHubHelpers
{
    /**
     * @param  null  $abstract
     * @return Container|mixed|object
     *
     * @throws BindingResolutionException
     */
    protected function app($abstract = null, array $parameters = [])
    {
        if (function_exists('app')) {
            return app($abstract, $parameters);
        }

        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract, $parameters);
    }

    /**
     * @throws AuthenticationException
     */
    protected function setToken(Request $request): string
    {
        $token = $request->bearerToken();

        if (is_null($token)) {
            $this->throw(__('The bearer token is required.'));
        }

        return $token;
    }

    /**
     * @param  null  $previous
     *
     * @throws AuthenticationException
     */
    protected function throw(string $message, $previous = null): void
    {
        throw new AuthenticationException($message, 0, $previous);
    }
}
