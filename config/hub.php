<?php

use App\Models\User;
use BildVitta\Hub\Entities\HubBrand;
use BildVitta\Hub\Entities\HubCompany;
use BildVitta\Hub\Entities\HubPosition;
use BildVitta\Hub\Entities\HubUserCompany;
use BildVitta\Hub\Entities\HubUserCompanyParentPosition;
use BildVitta\Hub\Entities\HubUserCompanyRealEstateDevelopment;

return [
    'base_uri' => env('MS_HUB_BASE_URI', 'https://hub-server.nave.dev.br'),

    'front_uri' => env('MS_HUB_FRONT_URI', 'https://hub.nave.dev.br'),

    'prefix' => env('MS_HUB_API_PREFIX', '/api'),

    'model_user' => User::class,
    'model_user_key' => 'uuid',
    'model_company' => HubCompany::class,
    'model_position' => HubPosition::class,
    'model_user_company' => HubUserCompany::class,
    'model_user_company_parent_position' => HubUserCompanyParentPosition::class,
    'model_user_company_real_estate_development' => HubUserCompanyRealEstateDevelopment::class,
    'model_brand' => HubBrand::class,

    'api_version' => env('MS_HUB_API_VERSION', '1'),

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

    'db' => [
        'host' => env('MS_HUB_DB_HOST'),
        'port' => env('MS_HUB_DB_PORT'),
        'database' => env('MS_HUB_DB_DATABASE'),
        'username' => env('MS_HUB_DB_USERNAME'),
        'password' => env('MS_HUB_DB_PASSWORD'),
    ],

];
