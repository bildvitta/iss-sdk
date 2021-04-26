<?php
/** @noinspection PhpUndefinedClassInspection */

namespace BildVitta\Hub\Middleware;

use BildVitta\Hub\Entities\HubUser;
use BildVitta\Hub\Exceptions\AuthenticationException;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
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
    private ?string $bearerToken;

    /**
     * @var Auth
     */
    private $authService;

    /**
     * @var Config
     */
    private $configService;

    /**
     * @var Cache
     */
    private $cacheService;

    /**
     * @var HubUser
     */
    private HubUser $hubUserModel;

    /**
     * Hashing token with md5, that will be saved in the table 'hub_users'.
     *
     * @var string
     */
    private string $bearerTokenHash;

    /**
     * @var string
     */
    private string $cacheKey;

    /**
     * Test constructor.
     */
    public function __construct()
    {
        $this->authService = app('auth');
        $this->configService = app('config');
        $this->cacheService = app('cache');
        $this->hubUserModel = new HubUser();
    }

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

        $this->bearerTokenHash = md5($this->bearerToken);
        $this->cacheKey = 'access_token_user_id_' . $this->bearerTokenHash;

        try {
            $this->loginByCache();
        } catch (ModelNotFoundException $modelNotFoundException) {
            try {
                $apiUser = $this->getUser();

                $user = $this->updateOrCreateUser($apiUser);

                $this->hubUserModel->create(['token' => $this->bearerTokenHash, 'user_id' => $user->id]);

                $this->cacheService->put($this->cacheKey, $user->id);

                $this->loginByUserId($user->id);
            } catch (RequestException $requestException) {
                $this->throw(__('Não foi possível autenticar o access_token.'), $requestException);
            }
        }

        if ($this->authService->guest()) {
            $this->throw(__('Não foi possível autenticar o access_token.'));
        }

        return $next($request);
    }

    /**
     * @param  Request  $request
     *
     * @return void
     *
     * @throws AuthenticationException
     */
    private function setToken(Request $request): void
    {
        $token = $request->bearerToken();

        if (is_null($token)) {
            $this->throw(__('Bearer token é obrigatório.'));
        }

        $this->bearerToken = $token;
    }

    /**
     * @param  string  $message
     * @param  Throwable|null  $previous
     *
     * @return void
     *
     * @throws AuthenticationException
     */
    private function throw(string $message, ?Throwable $previous = null): void
    {
        throw new AuthenticationException($message, 0, $previous);
    }

    /**
     * @return Authenticatable
     */
    private function loginByCache(): Authenticatable
    {
        $userId = $this->cacheService->rememberForever($this->cacheKey,
            function () {
                return $this->hubUserModel->whereToken($this->bearerTokenHash)->firstOrFail()->user_id;
            }
        );

        return $this->loginByUserId($userId);
    }

    /**
     * @param  int  $userId
     *
     * @return Authenticatable
     */
    private function loginByUserId(int $userId): Authenticatable
    {
        return app('auth')->loginUsingId($userId);
    }

    /**
     * @return stdClass
     */
    private function getUser(): stdClass
    {
        $response = app('hub', [$this->bearerToken])->users()->me();

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
            $user = $userModel
                ->whereEmail($apiUser->email)
                ->where(function (Builder $builder) use ($apiUser) {
                    $builder
                        ->where('hub_uuid', $apiUser->uuid)
                        ->orWhereNull('hub_uuid');
                }
                )->firstOrFail();

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
