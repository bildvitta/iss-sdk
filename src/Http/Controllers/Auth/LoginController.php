<?php


namespace BildVitta\Hub\Http\Controllers\Auth;

use BildVitta\Hub\Http\Requests\LoginRequest;
use Illuminate\Support\Str;

/**
 * Class LoginController
 * @package BildVitta\Hub\Http\Controllers\Auth
 */
class LoginController extends AuthController
{
    public function __invoke(LoginRequest $request)
    {
        $state = Str::random(40);
        $stateComplement = Str::random(12);

        cache()->put("state.{$stateComplement}", $state, now()->addMinutes(5));
        cache()->put($state, $request->get('url'), now()->addMinutes(5));

        $query = http_build_query([
            'client_id' => config('hub.oauth.client_id'),
            'redirect_uri' => config('hub.oauth.redirect'),
            'response_type' => 'code',
            'scope' => config('hub.oauth.scopes'),
            'state' => "{$state}.{$stateComplement}",
            'url' => $request->get('url', '/')
        ]);

        $redirect_uri = config('hub.front_uri') . config('hub.oauth.authorize_uri') . '?' . $query;

        return response()->json(
            [
                'login_url' => $redirect_uri
            ]
        );
    }
}
