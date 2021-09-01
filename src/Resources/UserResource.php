<?php

namespace BildVitta\Hub\Resources;

use BildVitta\Hub\Contracts\Resources\UserResourceContract;
use BildVitta\Hub\Hub;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;

/**
 * Class UserResource.
 *
 * @package BildVitta\Hub\Resources
 */
class UserResource extends Resource implements UserResourceContract
{
    /**
     * @const string
     */
    private const PREFIX = '/users';

    /**
     * @const string
     */
    private const ENDPOINT_ME = self::PREFIX . '/me';

    /**
     * @var Hub
     */
    private Hub $hub;

    /**
     * UserResource constructor.
     *
     * @param Hub $hub
     */
    public function __construct(Hub $hub)
    {
        $this->hub = $hub;
    }

    /**
     * User data token informed.
     *
     * @return Response.
     *
     * @throws RequestException
     */
    public function me(): Response
    {
        $params = [
            'project' => Config::get('app.slug', '')
        ];
        $endpoint = self::ENDPOINT_ME . '?' . http_build_query($params);
        return $this->hub->request->get($endpoint)->throw();
    }
}
