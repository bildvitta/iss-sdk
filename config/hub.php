<?php

return [

    'base_uri' => env('MS_HUB_BASE_URI', 'https://api.almobi.com.br'),

    'prefix' => env('MS_HUB_API_PREFIX', '/api'),

    'model_user' => '\App\Entities\User',

    'model_company' => '\BildVitta\Hub\Entities\HubCompany::class'
];
