<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $userCompanyModel = app(config('hub.model_user_company'));
        $userCompanyRealEstateDevelopmentModel = app(config('hub.model_user_company_real_estate_development'));

        Schema::create($userCompanyRealEstateDevelopmentModel->getTable(), function (Blueprint $table) use ($userCompanyModel) {
            $table->id();
            $table->foreignId('user_company_id')->constrained($userCompanyModel->getTable())->cascadeOnDelete()->index('hucred_user_company_id_foreign');
            $table->uuid('real_estate_development_uuid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $userCompanyRealEstateDevelopmentsModel = app(config('hub.model_user_company_real_estate_development'));
        Schema::dropIfExists($userCompanyRealEstateDevelopmentsModel->getTable());
    }
};
