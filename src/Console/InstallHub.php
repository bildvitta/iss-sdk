<?php

/** @noinspection PhpMissingFieldTypeInspection */

namespace BildVitta\Hub\Console;

use BildVitta\Hub\HubServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class InstallHubPackage.
 */
class InstallHub extends Command
{
    /**
     * Arguments to vendor config publish.
     *
     * @const array
     */
    private const VENDOR_PUBLISH_CONFIG_PARAMS = [
        '--provider' => HubServiceProvider::class,
        '--tag' => 'hub-config',
    ];

    /**
     * Publicando migrações do Laravel Permissions.
     *
     * @const array
     */
    private const VENDOR_PUBLISH_MIGRATION_PARAMS = [
        '--provider' => \Spatie\Permission\PermissionServiceProvider::class,
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hub:install';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Install the ISS';

    /**
     * @return void
     */
    public function handle()
    {
        $this->info('Installing Hub...');

        $this->info('Publishing configuration...');

        if (! $this->configExists('hub.php')) {
            $this->publishConfiguration();
            $this->info('Published configuration');
        } elseif ($this->shouldOverwriteConfig()) {
            $this->info('Overwriting configuration file...');
            $this->publishConfiguration($force = true);
        } else {
            $this->info('Existing configuration was not overwritten');
        }

        $this->info('Finish configuration!');

        $this->info('Publishing migration...');

        if ($this->shouldRunMigrations()) {
            $this->publishMigration();
        }

        $this->info('Finish migration!');

        $this->runMigrations();

        $this->info('Installed HubPackage');
    }

    private function configExists(string $fileName): bool
    {
        return File::exists(config_path($fileName));
    }

    /**
     * @param  bool|false  $forcePublish
     */
    private function publishConfiguration($forcePublish = false): void
    {
        $params = self::VENDOR_PUBLISH_CONFIG_PARAMS;

        if ($forcePublish === true) {
            $params['--force'] = '';
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Should overwrite config file.
     */
    private function shouldOverwriteConfig(): bool
    {
        return $this->confirm('Config file already exists. Do you want to overwrite it?', false);
    }

    private function shouldRunMigrations(): bool
    {
        return $this->confirm('Run migrations of Laravel Permissions package? If you have already done this step, do not do it again!');
    }

    private function publishMigration(): void
    {
        $this->call('vendor:publish', self::VENDOR_PUBLISH_MIGRATION_PARAMS);
    }

    private function runMigrations()
    {
        $this->info('Run migrations.');
        $this->call('migrate');
        $this->info('Finish migrations.');
    }
}
