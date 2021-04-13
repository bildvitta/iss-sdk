<?php

namespace BildVitta\Hub;

use BildVitta\Hub\Resources\AuthResource;
use BildVitta\Hub\Resources\UserResource;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Class Hub.
 *
 * @package BildVitta\Hub
 */
class Hub extends Factory
{
    /**
     * @const array
     */
    private const DEFAULT_HEADERS = [
        'content-type' => 'application/json',
        'accept' => 'application/json',
        'User-Agent' => 'ISS v0.0.1-alpha'
    ];

    /**
     * @const array
     */
    private const DEFAULT_OPTIONS = ['allow_redirects' => false];

    /**
     * @var string
     */
    public string $baseUrl;

    /**
     * @var PendingRequest
     */
    public PendingRequest $request;

    /**
     * @var ?string
     */
    private ?string $token;

    /**
     * Hub constructor.
     *
     * @param  ?string  $token
     */
    public function __construct(?string $token = null)
    {
        parent::__construct();

        $this->token = $token;

        $this->request = $this->prepareRequest();
    }

    /**
     * @return PendingRequest
     */
    private function prepareRequest(): PendingRequest
    {
        $baseUrl = config('hub.base_uri') . config('hub.prefix');

        return $this->request = Http::withToken($this->token)
            ->baseUrl($baseUrl)
            ->withOptions(self::DEFAULT_OPTIONS)
            ->withHeaders(self::DEFAULT_HEADERS);
    }

    /**
     * @return AuthResource
     */
    public function auth(): AuthResource
    {
        return new AuthResource($this);
    }

    /**
     * @return UserResource
     */
    public function users(): UserResource
    {
        return new UserResource($this);
    }

    /**
     * @param  string  $token
     *
     * @return Hub
     */
    public function setToken(string $token): Hub
    {
        $this->token = $token;

        $this->prepareRequest();

        return $this;
    }
}