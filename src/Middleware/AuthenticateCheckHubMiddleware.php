<?php

namespace BildVitta\Hub\Middleware;

use BildVitta\Hub\Middleware\Helpers\AuthenticateHubHelpers;
use BildVitta\Hub\Traits\LoginUser;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AuthenticateCheckHubMiddleware extends AuthenticateHubHelpers
{
    use LoginUser;

    public function handle(Request $request, Closure $next)
    {
        $token = $this->setToken($request);
        $md5Token = md5($token) . '-check';

        try {
            $cache = $this->getOrSetCredentialsCache($md5Token, $token);
            $user = $this->getOrSetUserCache($md5Token, $cache);

            $this->loginByUserId($user->id);
        } catch (\Exception $e) {
            Cache::delete($md5Token);
            report($e);
            return response()->json([
                'status' => [
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'text' => json_encode($e->getMessage()),
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }

    private function getOrSetCredentialsCache(string $md5Token, string $token)
    {
        return Cache::remember($md5Token, 60 * 60, function () use ($token) {
            $response = $this->checkCredentials($token);
            if ($response->status() !== Response::HTTP_OK) {
                throw new \Exception(__('Unable to authenticate bearerToken.'));
            }
            return json_decode($response->body());
        });
    }

    private function getOrSetUserCache(string $md5Token, $cache)
    {
        $userModel = $this->app('config')->get('hub.model_user');
        return Cache::remember($md5Token . '-user', 60 * 60, function () use ($userModel, $cache) {
            return $userModel::whereHubUuid($cache->result->uuid)->first();
        });
    }

    private function checkCredentials(string $token)
    {
        $url = $this->app('config')->get('hub.base_uri')
            . $this->app('config')->get('hub.prefix')
            . $this->app('config')->get('hub.oauth.userinfo_uri');

        return Http::withHeaders([
            'Accept' => 'application/json',
        ])->get($url);
    }
}
