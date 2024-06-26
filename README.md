[![Latest Version on Packagist](https://img.shields.io/packagist/v/bildvitta/iss-sdk.svg?style=flat-square)](https://packagist.org/packages/bildvitta/iss-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/bildvitta/iss-sdk.svg?style=flat-square)](https://packagist.org/packages/bildvitta/iss-sdk)

# Introduction

The ISS (International Space Station) aims to be a space station (`client`) of connection between the microservices of
its ecosystem and the authentication and permissions microservice of the user that here is called in the script as
Hub.permissions modules / microservices (Hub)

# Installation

You can install the package via composer:

```bash
composer require bildvitta/iss-sdk:dev-develop
```

For everything to work perfectly in addition to having the settings file published in your application, run the command
below:

```bash
php artisan hub:install
```

# Configuration

This is the contents of the published config file:

```php
return [
    'base_uri' => env('MS_HUB_BASE_URI', 'https://api-dev-hub.nave.dev'),

    'front_uri' => env('MS_HUB_FRONT_URI', 'https://develop.hub.nave.dev'),

    'prefix' => env('MS_HUB_API_PREFIX', '/api'),

    'model_user' => '\App\Entities\User',

    'model_company' => '\BildVitta\Hub\Entities\HubCompany::class',

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
    ]
];
```

With the configuration file `` hub.php`` published in your configuration folder it is necessary to create environment
variables in your `` .env`` file:

```dotenv
MS_HUB_BASE_URI="https://api-dev-hub.nave.dev"

MS_HUB_PREFIX="/api"
```

## Change permission and role model from spatie/laravel-permissions

You should change the default spatie/laravel-permissions models to ours, as we have some substantial changes to the use of Role and Permission.

```php
// config/permission.php

return [
    'models' = [
        'permission' => \BildVitta\Hub\Entities\HubPermission::class,
        'role' => \BildVitta\Hub\Entities\HubRole::class,
    ]
];
```

If you already have a change to these models, just extend our classes to have the correct functionalities.

## Add Trait on User Model

And remember to add the `BildVitta\Hub\Traits\User\HasCompanyLinks` Trait in the Users model.

```php
// \App\Models\User

use BildVitta\Hub\Traits\User\HasCompanyLinks;

class User extends Authenticatable
{
    use HasCompanyLinks;
    ...
}
```

Remembering that this trait already has `Spatie\Permission\Traits\HasRoles` by default, so you can remove the `Spatie\Permission\Traits\HasRoles` trait from your user model.

# Usage

All requests made to the ISS Service will return an instance
of [``\Illuminate\Http\Client\Response``](https://laravel.com/api/8.x/Illuminate/Http/Client/Response.html), which
implements the PHP `` ArrayAccess`` interface, allowing you to access JSON response data directly in the response

This also means that a variety of methods that can be used to inspect the response, follow some below:

````php
$response = Hub::setToken('jwt')->auth()->permissions();

$response->body(); // string;
$response->json(); // array|mixed;
$response->collect(); // Illuminate\Support\Collection;
$response->status(); // int;
$response->ok(); // bool;
$response->successful(); // bool;
$response->failed(); // bool;
$response->serverError(); // bool;
$response->clientError(); // bool;
$response->header('content-type'); // string;
$response->headers(); // array;
````

## Initialize ISS Service.

As there are several ways to program, there are also several ways to start the ISS Service.

Below are some ways to start the Service.

```php
$token = 'jwt';

$hub = app('hub', [$token]); // instance 2
$hub = app('hub')->setToken($token); // instance 1
$hub = new \BildVitta\Hub\Hub($token); // instance 3
$hub = (new \BildVitta\Hub\Hub())->setToken($token); // instance 4
$hub = BildVitta\Hub\Facades\Hub::setToken($token); // instance 1

```

## Authenticating User

To authenticate the Hub user in your module, it is necessary to use the
middleware `hub.auth = \ BildVitta \ Hub \ Middleware \ AuthenticateHubMiddleware`.

It will validate the token and create, if it does not exist, the user of the token in its user table.

````php
Route::middleware('hub.auth')->get('/users/me', function () {
    return auth()->user()->toArray();
});
````

When we installed the package, we created the `hub_uuid` column in your user table.

Tf it is not possible to authenticate, the middleware will return 401.

## User Authenticated

To access the token's user data directly, there is the ``\BildVitta\Hub\Contracts\Resources\AuthResourceContract``
interface

### Check Token

It is verified whether the token passed by parameter or previously loaded in the ISS Service is valid.

Example of use:

```php
try {
    Hub::auth()->check('jwt');
} catch (RequestException $requestException) {
    throw new Exception('invalid token');
}
```

### Get Permissions

It is possible to obtain ALL the permissions of the token uploaded to the ISS Service.

Example of use:

```php
try {
    $permissions = Hub::setToken('jwt')->auth()->permissions()['results']; // Implements `ArrayAccess`
    
    foreach ($permissions as $permission) {
        #TODO
    }
} catch (RequestException $requestException) {
    #TODO
}
```

### Adding permission scope to entity listing.

Now we have added a scope that filters by the permission level of the logged in user. To use it is very simple, just add
in the global scopes the PermissionScope class passing the permission that the user has to have, and then the magic
happens ;D

Code example:

```php
use BildVitta\Hub\Scopes\PermissionScope;

$query = RealEstateDevelopment::query();
$query->withGlobalScope('permission', new PermissionScope('real_estate_developments.show'));

$count = $query->count();
$query->pagination();

return (new RealEstateDevelopmentResource('index', $query->get()))->count($count);
```

Remembering that the scope name has to be permission, if not, it doesn't work <3

### Notifications

Add the `ABLY_KEY` key as an environment variable (ask your coordinator for this key)

```env
ABLY_KEY=your-ably-key
```

Then, set the BROADCAST_CONNECTION environment variable to ably in your application's .env file:

```env
BROADCAST_CONNECTION=ably
```

Check the `routes/channels.php` file if the private channel authentication route is correct.

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('notifications.{uuid}', function ($user, $uuid) {
    return (string) $user->uuid === (string) $uuid;
});
```

To finish, go to the BroadcastServiceProvider file and change it to this code.

```php
Broadcast::routes([
    'middleware' => ['hub.check'],
    'prefix' => 'api',
]);
```

Ensure that the $user->uuid is the same as that used in the hub, otherwise it may result in a 403 in this private channel authentication api.

## Testing

coming soon...

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Jean C. Garcia](https://github.com/SOSTheBlack)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
