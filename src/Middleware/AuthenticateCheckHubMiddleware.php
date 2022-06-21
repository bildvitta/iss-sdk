<?php

namespace BildVitta\Hub\Middleware;

use BildVitta\Hub\Middleware\Helpers\AuthenticateHubHelpers;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthenticateCheckHubMiddleware extends AuthenticateHubHelpers
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $this->setToken($request);
            $md5Token = md5($token);

            if (Cache::has(md5($md5Token))) {
                return $next($request);
            } else {
                $response = $this->checkCredentials($token);

                if ($response->status() != Response::HTTP_OK) {
                    $this->throw(__('Unable to authenticate bearerToken.'));
                }

                Cache::put($md5Token, $md5Token, Carbon::now()->addMinutes(5));
            }
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

    private function checkCredentials(string $token)
    {
        $url = $this->app('config')->get('hub.base_uri') . $this->app('config')->get('hub.prefix') . $this->app('config')->get('hub.oauth.userinfo_uri');
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->get($url)->throw();
    }
}
