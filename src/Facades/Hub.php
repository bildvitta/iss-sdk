<?php

/** @noinspection PhpUndefinedClassInspection */

namespace BildVitta\Hub\Facades;

use Illuminate\Support\Facades\Facade;
use RuntimeException;

/**
 * Class HubApi.
 */
class Hub extends Facade
{
    /**
     * @const string
     */
    private const ACCESSOR = 'hub';

    /**
     * Get the registered name of the component.
     *
     *
     * @throws RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return self::ACCESSOR;
    }
}
