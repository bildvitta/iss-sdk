<?php

namespace BildVitta\Hub\Resources;

use BildVitta\Hub\Hub;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;

class NotificationResource extends Resource
{
    /**
     * @const string
     */
    private const PREFIX = '/notifications';

    private Hub $hub;

    /**
     * NotificationResource constructor.
     */
    public function __construct(Hub $hub)
    {
        $this->hub = $hub;
    }

    public function createNotification(
        array $users,
        string $title,
        string $message,
        ?string $link = null,
    ): Response {
        $args = [
            'users_uuid' => $users,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'project_slug' => Config::get('app.slug', ''),
        ];
        $endpoint = self::PREFIX;

        return $this->hub->request->post($endpoint, $args)
            ->throw();
    }
}
