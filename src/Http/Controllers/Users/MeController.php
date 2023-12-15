<?php

namespace BildVitta\Hub\Http\Controllers\Users;

use BildVitta\Hub\Http\Requests\MeRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class MeController extends UsersController
{
    public function __invoke(MeRequest $request): Response
    {
        $params = [
            'project' => Config::get('app.slug', ''),
        ];
        $token_uri = Config::get('hub.base_uri').Config::get('hub.prefix').Config::get('hub.oauth.userinfo_uri').'?'.http_build_query($params);
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => $request->headers->get('Authorization'),
        ])->get(
            $token_uri
        );

        return new Response($response, $response->status());
    }
}
