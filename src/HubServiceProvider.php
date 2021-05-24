<?php

/** @noinspection PhpUndefinedClassInspection */

namespace BildVitta\Hub;

use BildVitta\Hub\Console\InstallHub;
use BildVitta\Hub\Middleware\AuthenticateHubMiddleware;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

/**
 * Class HubServiceProvider.
 *
 * @package BildVitta\Hub
 */
class HubServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/hub.php', 'hub');

        $this->app->singleton('hub', fn($app, $args) => new Hub($args[0] ?? null));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [__DIR__ . '/../config/hub.php' => config_path('hub.php')],
                'hub-config'
            );
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->commands([InstallHub::class]);


        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('hub.auth', AuthenticateHubMiddleware::class);
    }
}
