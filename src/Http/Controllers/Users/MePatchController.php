<?php

namespace BildVitta\Hub\Http\Controllers\Users;

use BildVitta\Hub\Http\Requests\MePatchRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class MePatchController extends UsersController
{
    public function __invoke(MePatchRequest $request)
    {
        $token_uri = Config::get('hub.base_uri') . Config::get('hub.prefix') . Config::get('hub.oauth.userinfo_uri');
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => $request->headers->get('Authorization')
        ])->patch(
            $token_uri,
            [
                'companies' => $request->get('companies'),
            ]
        );

        return new Response($response, $response->status());
    }
}
