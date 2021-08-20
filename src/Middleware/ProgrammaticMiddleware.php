<?php


namespace BildVitta\Hub\Middleware;

use BildVitta\Hub\Middleware\Helpers\AuthenticateHubHelpers;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProgrammaticMiddleware extends AuthenticateHubHelpers
{
    public function handle(Request $request, Closure $next)
    {
        # POG
        if ($request->header('Almobi-Host') == "Hub") {
            return $next($request);
        }

        try {
            $token = $this->setToken($request);

            $response = $this->checkCredentials($token);
            if ($response->status() != Response::HTTP_NO_CONTENT) {
                $this->throw(__('Unable to authenticate bearerToken.'));
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
        $url = $this->app('config')->get('hub.base_uri') . $this->app('config')->get('hub.prefix') . '/programmatic/check';
        Log::debug($url);
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->get($url)->throw();
    }
}
