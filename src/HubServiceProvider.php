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

            if (! class_exists('addHubUuidColumnInUsersTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/add_hub_uuid_column_in_users_table.php.stub' => database_path(
                        'migrations/' . date('Y_m_d_His',time()) . '_add_hub_uuid_column_in_users_table.php'
                    )], 'hub-migration'
                );
            }
        }

        $this->commands([InstallHub::class]);

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('hub.auth', AuthenticateHubMiddleware::class);
    }
}
