<?php

namespace BildVitta\Hub\Traits;

trait UsesHubDB
{
    public function __construct(array $attributes = [])
    {
        $this->configDbConnection();
        parent::__construct($attributes);
    }

    public static function __callStatic($method, $parameters)
    {
        self::configDbConnection();

        return parent::__callStatic($method, $parameters);
    }

    protected static function configDbConnection()
    {
        config([
            'database.connections.iss-sdk' => [
                'driver' => 'mysql',
                'url' => config('hub.db.url'),
                'host' => config('hub.db.host'),
                'port' => config('hub.db.port'),
                'database' => config('hub.db.database'),
                'username' => config('hub.db.username'),
                'password' => config('hub.db.password'),
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
                'options' => [],
            ],
        ]);
    }
}
