<?php

namespace BildVitta\Hub\Resources;

use BildVitta\Hub\Contracts\Resources\UserResourceContract;
use BildVitta\Hub\Hub;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

/**
 * Class UserResource.
 *
 * @package BildVitta\Hub\Resources
 */
class UserResource implements UserResourceContract
{
    /**
     * @const string
     */
    private const PREFIX = '/users';

    /**
     * @const string
     */
    private const ENDPOINT_ME = self::PREFIX . '/me';

    private const ENDPOINT_COMPANY = self::PREFIX . '/me';
    /**
     * @var Hub
     */
    private Hub $hub;

    /**
     * UserResource constructor.
     *
     * @param  Hub  $hub
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
        return $this->hub->request->get(self::ENDPOINT_ME)->throw();
    }

    public function company(): Response
    {
        return  $this->hub->request->get()
    }
}