<?php


namespace BildVitta\Hub\Http\Controllers\Auth;

use BildVitta\Hub\Entities\HubUser;
use BildVitta\Hub\Http\Requests\LogoutRequest;
use Illuminate\Support\Facades\Http;

/**
 * Class LogoutController
 * @package BildVitta\Hub\Http\Controllers\Auth
 */
class LogoutController extends AuthController
{
    public function __invoke(LogoutRequest $request)
    {
        $logout_uri = config('hub.base_uri') . config('hub.prefix') . '/auth/logout';
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $request->bearerToken()
        ])->post($logout_uri);

        HubUser::where('user_id', '=', $request->user()->id)->delete();
        return $response->json();
    }
}
