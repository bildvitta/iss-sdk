<?php

namespace BildVitta\Hub\Logging;

use Exception;
use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;


class SatelliteX9Handler extends AbstractProcessingHandler
{
    protected array $config = [];

    /**
     * @param int $level
     * @param array $config
     */
    public function __construct($level = Logger::DEBUG, array $config = [])
    {
        parent::__construct($level);
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function handleBatch(array $records): void
    {
        foreach ($records as $record) {
            $this->write($record);
        }
    }

    /**
     * @param array $record
     */
    protected function write(array $record): void
    {
        $with_config = $this->config['with'];

        $data = [
            'user_uuid' => $record['user_uuid'],
            'action' => $record['context']['action'] ?? null,
            'description' => $record['message'],
            'entity' => $record['context']['entity'] ?? null,
            'entity_uuid' => $record['context']['entity_uuid'] ?? null,
            'module' => $with_config['app_module'],
            'request' => $record['level_name']
        ];

        try {
            $url = $with_config['app_url'] . $with_config['app_endpoint'];
            $response = Http::post($url, $data);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
