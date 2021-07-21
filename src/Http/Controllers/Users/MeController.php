<?php


namespace BildVitta\Hub\Http\Controllers\Users;

use BildVitta\Hub\Http\Requests\MeRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class MeController extends UsersController
{
    public function __invoke(MeRequest $request)
    {
        $token_uri = config('hub.base_uri') . config('hub.prefix') . config('hub.oauth.userinfo_uri');
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => $request->headers->get('Authorization')
        ])->get(
            $token_uri
        );

        return new Response($response, $response->status());
    }
}
