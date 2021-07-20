<?php


namespace BildVitta\Hub\Http\Controllers\Auth;

use BildVitta\Hub\Entities\HubOauthToken;
use BildVitta\Hub\Http\Requests\LoginRequest;
use BildVitta\Hub\Http\Requests\RefreshRequest;
use Illuminate\Support\Facades\Http;

/**
 * Class RefreshController
 * @package BildVitta\Hub\Http\Controllers\Auth
 */
class RefreshController extends AuthController
{
    public function __invoke(RefreshRequest $request)
    {
        $bearerToken = $request->bearerToken();
        $bearerTokenCache = cache()->get(md5($bearerToken));

        if (is_null($bearerTokenCache)) {
            $loginUrl = new LoginController;
            return $loginUrl(new LoginRequest);
        }

        if (!$bearerTokenCache->is_expired()) {
            $refresh_uri = config('hub.base_uri') . config('hub.oauth.token_uri');
            $response = Http::asForm()->post($refresh_uri, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $bearerTokenCache->refresh_token,
                'client_id' => config('hub.oauth.client_id'),
                'client_secret' => config('hub.oauth.client_secret'),
                'scope' => config('hub.oauth.scopes'),
            ]);

            $jsonCache = $response->json();
            $jsonCache['expires_in_dt'] = now()->addSeconds($response->json('expires_in'));
            cache()->put(
                md5($response->json('access_token')),
                new HubOauthToken($jsonCache),
                now()->addSeconds($response->json('expires_in'))
            );
            
            return response()->json([
                'access_token' => $response->json('access_token')
            ]);
        }

        return response()->json([
            'access_token' => $bearerTokenCache->access_token
        ]);
    }
}
