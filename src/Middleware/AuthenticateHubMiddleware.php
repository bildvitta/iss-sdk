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
use Illuminate\Support\Facades\Cache;
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
            Log::info([
                'class::function' => 'AuthenticateHubMiddleware::handle',
                'payload' => [
                    'request' => $request->all(),
                    'Authorization' => $request->headers->get('Authorization'),
                    'user' => $request->user()
                ],
            ]);

            $token = $this->setToken($request);

            $cacheHash = md5($token);
            $cacheKey = 'access_token_user_id_' . $cacheHash;

            $user = Cache::remember(config('hub.cache.prefix') . 'me.middleware.' . $cacheKey, config('hub.cache.ttl'), function () use ($cacheHash, $cacheKey, $token) {
                return $this->loginUserByCache($cacheHash, $cacheKey, $token);
            });
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => [
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'text' => json_encode($e->getMessage())
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
