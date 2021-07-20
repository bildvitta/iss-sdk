<?php


namespace BildVitta\Hub\Http\Controllers\Auth;

use BildVitta\Hub\Entities\HubUser;
use BildVitta\Hub\Http\Requests\LogoutRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

/**
 * Class LogoutController
 * @package BildVitta\Hub\Http\Controllers\Auth
 */
class LogoutController extends AuthController
{
    public function __invoke(LogoutRequest $request)
    {
        $token_uri = config('hub.base_uri') . config('hub.prefix') . '/auth/logout';
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => $request->headers->get('Authorization')
        ];
        $response = Http::withHeaders($headers)->post($token_uri);
        $responseJson = $response->json();
        if ($response->status() == Response::HTTP_OK) {
            $responseJson['url'] = config('hub.front_uri');
        }

        HubUser::where('user_id', '=', $request->user()->id)->delete();
        return new Response($responseJson, $response->status());
    }
}
