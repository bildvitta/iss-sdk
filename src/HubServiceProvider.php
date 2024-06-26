<?php

/** @noinspection PhpUndefinedClassInspection */

namespace BildVitta\Hub;

use BildVitta\Hub\Console\CleanPermissions;
use BildVitta\Hub\Console\InstallHub;
use BildVitta\Hub\Middleware\AuthenticateCheckHubMiddleware;
use BildVitta\Hub\Middleware\AuthenticateHubMiddleware;
use BildVitta\Hub\Middleware\ProgrammaticMiddleware;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

/**
 * Class HubServiceProvider.
 */
class HubServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/hub.php', 'hub');

        $this->app->singleton('hub', fn ($app, $args) => new Hub($args[0] ?? request()->bearerToken()));
    }

    /**
     * Bootstrap any application services.
     *
     *
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [__DIR__.'/../config/hub.php' => config_path('hub.php')],
                'hub-config'
            );
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->commands([InstallHub::class, CleanPermissions::class]);

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('hub.auth', AuthenticateHubMiddleware::class);
        $router->aliasMiddleware('hub.check', AuthenticateCheckHubMiddleware::class);
        $router->aliasMiddleware('hub.programmatic', ProgrammaticMiddleware::class);
    }
}
