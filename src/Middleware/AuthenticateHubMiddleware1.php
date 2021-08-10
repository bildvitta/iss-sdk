<?php

namespace BildVitta\Hub\Middleware;

use BildVitta\Hub\Entities\HubUser;
use BildVitta\Hub\Exceptions\AuthenticationException;
use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthAttemptMiddleware.
 *
 * @package BildVitta\Hub\Middleware
 */
class AuthenticateHubMiddleware1
{
    /**
     * Token JWT.
     *
     * @var string|null
     */
    private ?string $bearerToken;

    /**
     * @var AuthManager
     */
    private AuthManager $authService;

    /**
     * @var Repository
     */
    private Repository $configService;

    /**
     * @var CacheManager
     */
    private CacheManager $cacheService;

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
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $this->setToken($request);

            $this->bearerTokenHash = md5($this->bearerToken);
            $this->cacheKey = 'access_token_user_id_' . $this->bearerTokenHash;

            try {
                $this->loginByCache();

                $this->updatePermissions();
            } catch (ModelNotFoundException $modelNotFoundException) {
                try {
                    $apiUser = $this->getUser();

                    $user = $this->updateOrCreateUser($apiUser);

                    $this->hubUserModel->create([
                        'token' => $this->bearerTokenHash,
                        'user_id' => $user->id
                    ]);

                    $this->cacheService->put($this->cacheKey, $user->id);
                    $this->updatePermissions();

                    $this->loginByUserId($user->id);
                } catch (RequestException $requestException) {
                    $this->throw(__('Não foi possível autenticar o access_token.'), $requestException);
                }
            }

            if ($this->authService->guest()) {
                $this->throw(__('Não foi possível autenticar o access_token.'));
            }
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => [
                        'code' => Response::HTTP_UNAUTHORIZED,
                        'text' => $exception->getMessage()
                    ]
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $next($request);
    }

    /**
     * @param Request $request
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
     * @param string $message
     * @param null $previous
     *
     * @return void
     *
     * @throws AuthenticationException
     */
    private function throw(string $message, $previous = null): void
    {
        throw new AuthenticationException($message, 0, $previous);
    }

    /**
     * @return Authenticatable
     */
    private function loginByCache(): Authenticatable
    {
        $userId = $this->cacheService->rememberForever(
            $this->cacheKey,
            fn () => $this->hubUserModel->whereToken($this->bearerTokenHash)->firstOrFail()->user_id
        );

        $userModel = app(config('hub.model_user'));
        $userModel->findOrFail($userId, ['id']);

        return $this->loginByUserId($userId);
    }

    /**
     * @param int $userId
     *
     * @return Authenticatable
     */
    private function loginByUserId(int $userId): Authenticatable
    {
        return app('auth')->loginUsingId($userId);
    }

    /**
     * @throws AuthenticationException
     */
    private function updatePermissions()
    {
        try {
            $this->cacheService->remember($this->cacheKey . '_permissions', now()->addDay(), function () {
                $permissions = $this->getPermissions();

                foreach ($permissions as $permission) {
                    Permission::findOrCreate($permission->name, $permission->guard_name);
                }

                if (!empty($permissions)) {
                    auth()->user()->givePermissionTo(... collect($permissions)->pluck('name')->toArray());
                    return true;
                }

                return false;
            });
        } catch (RequestException $requestException) {
            $this->throw(__('Não foi possível atualizar as permissões.'), $requestException);
            return false;
        }
    }

    /**
     * @return array
     *
     * @throws RequestException
     */
    private function getPermissions(): array
    {
        $response = app('hub', [$this->bearerToken])->auth()->permissions();

        return $response->object()->results;
    }

    /**
     * @return stdClass
     *
     * @throws RequestException
     */
    private function getUser(): stdClass
    {
        $response = app('hub', [$this->bearerToken])->users()->me();

        return $response->object()->result;
    }

    /**
     * @param stdClass $apiUser
     *
     * @return Authenticatable
     */
    private function updateOrCreateUser(stdClass $apiUser): Authenticatable
    {
        $userModel = app(config('hub.model_user'));
        $companyModel = app(config('hub.model_company'));

        try {
            $user = $userModel
                ->whereEmail($apiUser->email)
                ->where(
                    function (Builder $builder) use ($apiUser) {
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

        $hubCompany = $this->getCompany($apiUser->company);
        try {
            $company = $companyModel::where('uuid', '=', $apiUser->company)->firstOrFail();
            $company->name = $hubCompany->name;
        } catch (ModelNotFoundException $modelNotFoundException) {
            $company = new $companyModel();
            $company->uuid = $hubCompany->uuid;
            $company->name = $hubCompany->name;
        }

        $company->saveOrFail();

        $user->company_id = $company->id;

        $user->saveOrFail();
        $user->refresh();

        return $user;
    }

    private function getCompany(string $companyUuid): stdClass
    {
        $response = app('hub', [$this->bearerToken])->companies()->findByUuid($companyUuid);

        return $response->object()->result;
    }
}
