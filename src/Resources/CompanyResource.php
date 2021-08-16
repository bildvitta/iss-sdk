<?php

namespace BildVitta\Hub\Resources;

use BildVitta\Hub\Hub;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

/**
 * Class CompanyResource.
 *
 * @package BildVitta\Hub\Resources
 */
class CompanyResource extends Resource
{
    /**
     * @const string
     */
    private const PREFIX = '/programmatic/companies';

    /**
     * @const string
     */
    private const ENDPOINT_FIND_BY_UUID = self::PREFIX . '/%s';

    /**
     * @var Hub
     */
    private Hub $hub;

    /**
     * AuthResource constructor.
     *
     * @param Hub $hub
     */
    public function __construct(Hub $hub)
    {
        $this->hub = $hub;
    }

    /**
     * Checks if the token passed by parameter or previously loaded in the ISS Service is valid.
     *
     * @param string $uuid
     *
     * @return Response
     *
     * @throws RequestException
     */
    public function findByUuid(string $uuid): Response
    {
        $url = vsprintf(self::ENDPOINT_FIND_BY_UUID, [$uuid]);

        $hubUrl = $this->hub->setToken('', true);

        return $hubUrl->request->get($url)->throw();
    }
}
