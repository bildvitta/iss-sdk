<?php

namespace BildVitta\Hub\Logging;

use Illuminate\Support\Facades\Auth;
use Monolog\Logger;

class SatelliteX9Logger
{
    /**
     * @param array $config
     * @return Logger
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('satelliteX9');

        $x9_handler = new SatelliteX9Handler(Logger::DEBUG, $config);

        $x9_handler->pushProcessor(function ($record) {
            $record['user_uuid'] = Auth::user() ? Auth::user()->uuid : null;
            return $record;
        });

        $logger->pushHandler($x9_handler);

        return $logger;
    }
}
