<?php


namespace BildVitta\Hub\Http\Controllers\Users;

use BildVitta\Hub\Http\Requests\MeRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MeController extends UsersController
{
    /**
     * @param MeRequest $request
     * @return Response
     */
    public function __invoke(MeRequest $request): Response
    {
        Log::info([
            'message' => 'MeController::__invoke',
            'request' => $request->all(),
            'Authorization' => $request->headers->get('Authorization'),
            'user' => $request->user(),
            'token_uri' => $this->getTokenUri()
        ]);

        return Cache::remember(config('hub.cache.prefix') . 'me.' . $request->user()->id, config('hub.cache.ttl'), function () use ($request) {
            return $this->getMe($request);
        });
    }

    protected function getMe(MeRequest $request): Response
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => $request->headers->get('Authorization')
        ])->get($this->getTokenUri());

        return new Response($response, $response->status());
    }

    protected function getTokenUri(): string
    {
        return Config::get('hub.base_uri') . Config::get('hub.prefix') . Config::get('hub.oauth.userinfo_uri') . '?' . http_build_query([
            'project' => Config::get('app.slug', '')
        ]);
    }
}
