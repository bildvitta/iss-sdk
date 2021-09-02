<?php

namespace BildVitta\Hub;

use BildVitta\Hub\Resources\AuthResource;
use BildVitta\Hub\Resources\CompanyResource;
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
     * @const string
     */
    public const PREFIX_PROGRAMMATIC = '/programmatic';

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
        $this->baseUrl = config('hub.base_uri').config('hub.prefix');

        return $this->request = Http::withToken($this->token)
            ->baseUrl($this->baseUrl)
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
     * @return CompanyResource
     */
    public function companies(): CompanyResource
    {
        return new CompanyResource($this);
    }

    /**
     * @param  string|null  $token
     * @param  bool         $programmatic
     *
     * @return Hub
     */
    public function setToken(?string $token = null, bool $programmatic = false): Hub
    {
        $this->token = $token;
        if ($programmatic) {
            $this->token = $this->getToken();
        }

        $this->prepareRequest();

        return $this;
    }

    private function getToken()
    {
        $hubUrl = config('hub.base_uri').config('hub.oauth.token_uri');
        $response = Http::asForm()->post($hubUrl, [
            'grant_type' => 'client_credentials',
            'client_id' => config('hub.programatic_access.client_id'),
            'client_secret' => config('hub.programatic_access.client_secret'),
            'scope' => '*',
        ]);

        return $response->throw()->json('access_token');
    }
}
