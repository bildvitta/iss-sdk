<?php

/** @noinspection PhpUndefinedClassInspection */

namespace BildVitta\Hub;

use BildVitta\Hub\Console\CleanPermissions;
use BildVitta\Hub\Console\InstallHub;
use BildVitta\Hub\Middleware\AuthenticateCheckHubMiddleware;
use BildVitta\Hub\Middleware\AuthenticateHubMiddleware;
use BildVitta\Hub\Middleware\ProgrammaticMiddleware;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;

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

        $this->registerRequestMacros();

        $this->callAfterResolving(Gate::class, function (Gate $gate, Application $app) {
            $gate->before(function (Authenticatable $user, string $ability, array $args) {
                if ($user->is_superuser) {
                    return true;
                }

                if (request()->checkVersion('2')) {
                    $model = Arr::first($args, fn ($arg) => is_string($arg));

                    if (is_string($model)) {
                        $companyModel = config('hub.model_company');
                        $model = app($companyModel)->with('main_company')->where('uuid', $model)->firstOrFail();
                    }

                    return $user->checkCompanyPermission($ability, $model);
                }

                if (method_exists($user, 'checkPermissionTo')) {
                    return $user->checkPermissionTo($ability, $guard ?? null) ?: null;
                }

                return true;
            });
        });
    }

    protected function registerRequestMacros()
    {
        Request::macro('version', function () {
            return config('hub.api_version');
        });

        Request::macro('checkVersion', function ($version) {
            return $this->version() === $version;
        });
    }
}
