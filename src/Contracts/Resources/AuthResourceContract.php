<?php

namespace BildVitta\Hub\Contracts\Resources;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

/**
 * Interface AuthResourceContract.
 */
interface AuthResourceContract
{
    /**
     * Checks if the token passed by parameter or previously loaded in the ISS Service is valid.
     *
     *
     *
     * @throws RequestException
     */
    public function check(string $token = ''): bool;

    /**
     * It is possible to obtain ALL the permissions of the token uploaded to the ISS Service.
     *
     *
     * @throws RequestException
     */
    public function permissions(): Response;
}
