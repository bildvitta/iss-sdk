<?php

namespace BildVitta\Hub\Middleware;

use BildVitta\Hub\Exceptions\AuthenticationException;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use stdClass;
use Throwable;

/**
 * Class AuthAttemptMiddleware.
 *
 * @package BildVitta\Hub\Middleware
 */
class AuthenticateHubMiddleware
{
    /**
     * Token JWT.
     *
     * @var string|null
     */
    private ?string $token;

    /**
     * @param  Request  $request
     * @param  Closure  $next
     *
     * @return mixed
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next)
    {
        $this->setToken($request);

        try {
            $apiUser = $this->getUser();

            $user = $this->updateOrCreateUser($apiUser);

            auth()->login($user);

            return $next($request);
        } catch (Throwable $e) {
            throw new AuthenticationException(__('Não foi possível realizar a autenticação.'),0, $e);
        }
    }

    /**
     * @param  Request  $request
     * @return void
     *
     * @throws AuthenticationException
     */
    private function setToken(Request $request): void
    {
        $token = $request->bearerToken();

        if (is_null($token)) {
            throw new AuthenticationException(__('Bearer token é obrigatório.'));
        }

        $this->token = $token;
    }

    /**
     * @return stdClass
     */
    private function getUser(): stdClass
    {
        $response = app('hub', [$this->token])->users()->me();

        return $response->object()->result;
    }

    /**
     * @param  stdClass  $apiUser
     *
     * @return Authenticatable
     */
    private function updateOrCreateUser(stdClass $apiUser): Authenticatable
    {
        $userModel = app(config('hub.model_user'));

        try {
            $user = $userModel->whereHubUuid($apiUser->uuid)->firstOrFail();

            $user->hub_uuid = $apiUser->uuid;
        } catch (ModelNotFoundException $modelNotFoundException) {
            $user = new $userModel();
            $user->hub_uuid = $apiUser->uuid;
            $user->name = $apiUser->name;
            $user->email = $apiUser->email;
            $user->password = bcrypt(uniqid(rand()));
        }

        $user->saveOrFail();

        return $user;
    }
}