<?php

namespace BildVitta\Hub\Contracts\Resources;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

/**
 * Interface UserResourceContract.
 */
interface UserResourceContract
{
    /**
     * User data token informed.
     *
     * @return Response.
     *
     * @throws RequestException
     */
    public function me(): Response;

    public function findByUuid(string $uuid, bool $programatic = false): Response;

    public function getByUuids(array $uuids, $attributes = [], bool $programatic = false): Response;

    public function search(array $query = [], bool $programatic = false): Response;
}
