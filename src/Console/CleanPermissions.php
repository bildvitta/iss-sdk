<?php

namespace BildVitta\Hub\Console;

use DB;
use Illuminate\Console\Command;

class CleanPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hub:clean-permissions';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Clean permissions from the database';

    public function handle()
    {
        $this->info('Cleaning permissions...');

        $this->info('Cleaning permissions from the database...');

        $this->cleanPermissions();

        $this->info('Permissions cleaned');
    }

    public function cleanPermissions()
    {
        DB::table('model_has_permissions')->delete();

        DB::table('model_has_roles')->whereNotIn('model_type', [
            'model_type' => 'BildVitta\\Hub\\Entities\\HubUserCompany',
        ])->delete();
    }
}
