<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $role = app(config('permission.models.role'));
        $hubCompany = app(config('hub.model_company'));
        
        Schema::table($role->getTable(), function (Blueprint $table) use ($hubCompany) {
            if (!Schema::hasColumn($table->getTable(), 'uuid')) {
                $table->uuid('uuid')->unique();
            }
            if (!Schema::hasColumn($table->getTable(), 'description')) {
                $table->string('description')->nullable();
            }
            if (!Schema::hasColumn($table->getTable(), 'hub_company_id')) {
                $table->foreignId('hub_company_id')->constrained($hubCompany->getTable())->cascadeOnDelete()->index('roles_hub_company_id_foreign');
            }
            if (!Schema::hasColumn($table->getTable(), 'has_all_real_estate_developments')) {
                $table->boolean('has_all_real_estate_developments')->default(false);
            }
            if (!Schema::hasColumn($table->getTable(), 'is_post_construction')) {
                $table->boolean('is_post_construction')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $role = app(config('permission.models.role'));

        Schema::table($role->getTable(), function (Blueprint $table) {
            if (Schema::hasColumn($table->getTable(), 'uuid')) {
                $table->dropColumn('uuid');
            }
            if (Schema::hasColumn($table->getTable(), 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn($table->getTable(), 'hub_company_id')) {
                $table->dropForeign(['hub_company_id']);
                $table->dropColumn('hub_company_id');
            }
            if (Schema::hasColumn($table->getTable(), 'has_all_real_estate_developments')) {
                $table->dropColumn('has_all_real_estate_developments');
            }
            if (Schema::hasColumn($table->getTable(), 'is_post_construction')) {
                $table->dropColumn('is_post_construction');
            }
        });
    }
};
