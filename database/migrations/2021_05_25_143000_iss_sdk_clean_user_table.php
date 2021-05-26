<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class IssSdkCleanUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumns('hub_users', ['company_uuid', 'company_name'])) {
            Schema::table('hub_users', function (Blueprint $table) {
                $table->dropColumn('company_uuid');
                $table->dropColumn('company_name');
            });
        }

        if (Schema::hasColumns('hub_users', ['role_permissions'])) {
            Schema::table('hub_users', function (Blueprint $table) {
                $table->dropColumn('role_permissions');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumns('hub_users', ['role_permissions'])) {
            Schema::table('hub_users', function (Blueprint $table) {
                $table->json('role_permissions')->nullable();
            });
        }

        if (!Schema::hasColumns('hub_users', ['company_uuid', 'company_name', 'company_email'])) {
            Schema::table('hub_users', function (Blueprint $table) {
                $table->uuid('company_uuid')->nullable();
                $table->string('company_name')->nullable();
            });
        }
    }
}
