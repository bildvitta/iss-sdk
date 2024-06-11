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
            $table->uuid('uuid')->unique();
            $table->string('description')->nullable();
            $table->foreignId('hub_company_id')->constrained($hubCompany->getTable())->cascadeOnDelete()->index('roles_hub_company_id_foreign');
            $table->boolean('has_all_real_estate_developments')->default(false);
            $table->boolean('is_post_construction')->default(false);
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
            $table->dropColumn('uuid');
            $table->dropColumn('description');
            $table->dropForeign(['hub_company_id']);
            $table->dropColumn('hub_company_id');
            $table->dropColumn('has_all_real_estate_developments');
            $table->dropColumn('is_post_construction');
        });
    }
};
