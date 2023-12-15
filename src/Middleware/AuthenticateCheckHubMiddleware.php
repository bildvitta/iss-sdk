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
        try {
            $token = $this->setToken($request);
            $md5Token = md5($token).'-check';

            if (! Cache::has($md5Token)) {
                $response = $this->checkCredentials($token);
                if ($response->status() != Response::HTTP_OK) {
                    $this->throw(__('Unable to authenticate bearerToken.'));
                }

                Cache::put($md5Token, json_decode($response->body()), Carbon::now()->addMinutes(60));
            }

            $cache = Cache::get($md5Token);
            $user = Cache::remember($md5Token.'-user', 60 * 60, function () use ($cache) {
                $userModel = $this->app('config')->get('hub.model_user');

                return $userModel::whereHubUuid($cache->result->uuid)->first();
            });

            $this->loginByUserId($user->id);
        } catch (Exception $e) {
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

    private function checkCredentials(string $token)
    {
        $url = $this->app('config')->get('hub.base_uri').$this->app('config')->get('hub.prefix').$this->app('config')->get('hub.oauth.userinfo_uri');

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->get($url)->throw();
    }
}
