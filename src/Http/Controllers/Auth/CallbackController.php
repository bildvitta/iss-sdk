<?php

namespace BildVitta\Hub\Http\Controllers\Auth;

use BildVitta\Hub\Entities\HubOauthToken;
use BildVitta\Hub\Http\Requests\CallbackRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CallbackController
 */
class CallbackController extends AuthController
{
    public function __invoke(CallbackRequest $request)
    {
        $stateExploded = Str::of($request->state)->explode('.');
        $stateFromParam = $stateExploded->first();
        $stateComplement = $stateExploded->last();

        $stateFromCache = cache()->pull("state.{$stateComplement}", '');
        $url = cache()->pull($stateFromCache);

        if (! (strlen($stateFromCache) > 0 && $stateFromCache === $stateFromParam)) {
            throw new NotFoundHttpException(__('State is not valid'));
        }

        $token_uri = config('hub.base_uri').config('hub.oauth.token_uri');

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->asForm()->post($token_uri, [
            'grant_type' => 'authorization_code',
            'client_id' => config('hub.oauth.client_id'),
            'client_secret' => config('hub.oauth.client_secret'),
            'redirect_uri' => config('hub.oauth.redirect'),
            'code' => $request->code,
        ]);

        $jsonCache = $response->json();
        $jsonCache['expires_in_dt'] = now()->addSeconds($response->json('expires_in'));
        cache()->put(
            md5($response->json('access_token')),
            new HubOauthToken($jsonCache),
            now()->addSeconds($response->json('expires_in'))
        );

        $json = $response->json();
        $json['redirect'] = $url;

        return $json;
    }
}
