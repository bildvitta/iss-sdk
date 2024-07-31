<?php

return [
    'base_uri' => env('MS_HUB_BASE_URI', 'https://api-dev-hub.nave.dev'),

    'front_uri' => env('MS_HUB_FRONT_URI', 'https://develop.hub.nave.dev'),

    'prefix' => env('MS_HUB_API_PREFIX', '/api'),

    'model_user' => \App\Models\User::class,
    'model_user_key' => 'uuid',
    'model_company' => \BildVitta\Hub\Entities\HubCompany::class,
    'model_position' => \BildVitta\Hub\Entities\HubPosition::class,
    'model_user_company' => \BildVitta\Hub\Entities\HubUserCompany::class,
    'model_user_company_parent_position' => \BildVitta\Hub\Entities\HubUserCompanyParentPosition::class,
    'model_user_company_real_estate_development' => \BildVitta\Hub\Entities\HubUserCompanyRealEstateDevelopment::class,
    'model_brand' => \BildVitta\Hub\Entities\HubBrand::class,

    'programatic_access' => [
        'client_id' => env('HUB_PROGRAMMATIC_CLIENT'),
        'client_secret' => env('HUB_PROGRAMMATIC_SECRET'),
    ],

    'oauth' => [
        'client_id' => env('HUB_CLIENT_ID', ''),
        'client_secret' => env('HUB_CLIENT_SECRET', ''),
        'redirect' => env('HUB_REDIRECT_URI', ''),
        'scopes' => env('HUB_SCOPE', 'profile'),

        'authorize_uri' => '/auth/authorize',
        'token_uri' => '/oauth/token',
        'userinfo_uri' => '/users/me',
        'notifications_uri' => '/users/me/notifications',
    ],

    'redirects' => [
        'userinfo_edit' => '/me',
    ],

];
