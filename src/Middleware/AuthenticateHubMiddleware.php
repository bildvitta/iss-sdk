<?php


namespace BildVitta\Hub\Middleware;

use BildVitta\Hub\Entities\HubUser;
use BildVitta\Hub\Middleware\Helpers\AuthenticateHubHelpers;
use BildVitta\Hub\Traits\LoginUser;
use Closure;
use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Class AuthenticateHubMiddleware
 * @package BildVitta\Hub\Middleware
 */
class AuthenticateHubMiddleware extends AuthenticateHubHelpers
{
    use LoginUser;

    private AuthManager $authService;
    private Repository $configService;
    private CacheManager $cacheService;
    private HubUser $hubUser;

    public function __construct()
    {
        $this->authService = $this->app('auth');
        $this->configService = $this->app('config');
        $this->cacheService = $this->app('cache');
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $this->setToken($request);

            $cacheHash = md5($token);
            $cacheKey = 'access_token_user_id_' . $cacheHash;

            $user = $this->loginUserByCache($cacheHash, $cacheKey, $token);
        } catch (Exception $e) {
            Log::driver('strerr')->error($e->getMessage());
            return response()->json([
                'status' => [
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'text' => $e->getMessage()
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
