<?php

namespace BildVitta\Hub\Resources;

use BildVitta\Hub\Contracts\Resources\AuthResourceContract;
use BildVitta\Hub\Hub;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

/**
 * Class AuthResource.
 */
class AuthResource extends Resource implements AuthResourceContract
{
    /**
     * @const string
     */
    private const PREFIX = '/auth';

    /**
     * @const string
     */
    private const ENDPOINT_CHECK = self::PREFIX.'/check';

    /**
     * @const string
     */
    private const ENDPOINT_GET_PERMISSIONS = '/auth'.'/permissions';

    private Hub $hub;

    /**
     * AuthResource constructor.
     */
    public function __construct(Hub $hub)
    {
        $this->hub = $hub;
    }

    /**
     * Checks if the token passed by parameter or previously loaded in the ISS Service is valid.
     *
     *
     *
     * @throws RequestException
     */
    public function check(string $token = ''): bool
    {
        if (is_string($token)) {
            $this->hub->setToken($token);
        }

        return $this->hub->request->get(self::ENDPOINT_CHECK)->throw();
    }

    /**
     * It is possible to obtain ALL the permissions of the token uploaded to the ISS Service.
     *
     *
     * @throws RequestException
     */
    public function permissions(): Response
    {
        return $this->hub->request->get(self::ENDPOINT_GET_PERMISSIONS)->throw();
    }
}
