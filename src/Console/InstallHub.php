<?php

namespace BildVitta\Hub\Console;

use BildVitta\Hub\HubServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class InstallHubPackage.
 *
 * @package BildVitta\Hub\Console
 */
class InstallHub extends Command
{
    /**
     * Arguments to vendor publish.
     *
     * @const array
     */
    private const VENDOR_PUBLISH_PARAMS = [
        '--provider' => HubServiceProvider::class,
        '--tag' => "hub-config"
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

        $this->runMigrations();

        $this->info('Installed HubPackage');
    }

    /**
     * @param  string  $fileName
     *
     * @return bool
     */
    private function configExists(string $fileName): bool
    {
        return File::exists(config_path($fileName));
    }

    /**
     * @param  bool|false  $forcePublish
     *
     * @return void
     */
    private function publishConfiguration($forcePublish = false): void
    {
        $params = self::VENDOR_PUBLISH_PARAMS;

        if ($forcePublish === true) {
            $params['--force'] = '';
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Should overwrite config file.
     *
     * @return bool
     */
    private function shouldOverwriteConfig(): bool
    {
        return $this->confirm('Config file already exists. Do you want to overwrite it?', false);
    }

    private function runMigrations()
    {
        $this->info('Run migrations.');
        $this->call('migrate', ['--path' => 'vendor\bildvitta\iss-sdk\database\migrations']);
        $this->info('Finish migrations.');
    }
}