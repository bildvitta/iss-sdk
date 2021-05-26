<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class IssSdkMigrations.
 */
class IssSdkMigrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumns('users', ['hub_uuid'])) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('hub_uuid')->nullable()->after('id');
            });
        }

        if (!Schema::hasTable('hub_users')) {
            Schema::create('hub_users', function (Blueprint $table) {
                $table->bigInteger('user_id');
                $table->char('token', 32);
            });
        }

        if (!Schema::hasColumns('hub_users', ['company_uuid', 'company_name'])) {
            Schema::table('hub_users', function (Blueprint $table) {
                $table->uuid('company_uuid')->nullable();
                $table->string('company_name')->nullable();
            });
        }

        if (!Schema::hasColumns('hub_users', ['role_permissions'])) {
            Schema::table('hub_users', function (Blueprint $table) {
                $table->json('role_permissions')->nullable();
            });
        }

        Schema::table('hub_users', function (Blueprint $table) {
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hub_users');
        Schema::dropColumns('users', ['hub_uuid']);
    }
}
