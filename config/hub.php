<?php

return [
    'base_uri' => env('MS_HUB_BASE_URI', 'https://api-dev-hub.nave.dev'),

    'front_uri' => env('MS_HUB_FRONT_URI', 'https://develop.hub.nave.dev'),

    'prefix' => env('MS_HUB_API_PREFIX', '/api'),

    'model_user' => \App\Models\User::class,

    'model_company' => \BildVitta\Hub\Entities\HubCompany::class,
    'model_position' => \BildVitta\Hub\Entities\Position::class,
    'model_user_company' => \BildVitta\Hub\Entities\UserCompany::class,
    'model_user_company_parent_position' => \BildVitta\Hub\Entities\UserCompanyParentPosition::class,
    'model_user_company_real_estate_developments' => \BildVitta\Hub\Entities\UserCompanyRealEstateDevelopments::class,

    'programatic_access' => [
        'client_id' => env('HUB_PROGRAMMATIC_CLIENT'),
        'client_secret' => env('HUB_PROGRAMMATIC_SECRET')
    ],

    'oauth' => [
        'client_id' => env('HUB_CLIENT_ID', ''),
        'client_secret' => env('HUB_CLIENT_SECRET', ''),
        'redirect' => env('HUB_REDIRECT_URI', ''),
        'scopes' => env('HUB_SCOPE', 'profile'),

        'authorize_uri' => '/auth/authorize',
        'token_uri' => '/oauth/token',
        'userinfo_uri' => '/users/me'
    ],

    'redirects' => [
        'userinfo_edit' => '/me'
    ]

];
