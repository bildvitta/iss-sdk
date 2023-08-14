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
        $userCompanyRealEstateDevelopmentsModel = app(config('hub.model_user_company_real_estate_developments'));

        Schema::create($userCompanyRealEstateDevelopmentsModel->getTable(), function (Blueprint $table) use ($userCompanyModel) {
            $table->id();
            $table->unsignedBigInteger('user_company_id');
            $table->uuid('real_estate_development_uuid');
            $table->foreign('user_company_id')->references('id')->on($userCompanyModel->getTable())->onDelete('cascade');
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
        $userCompanyRealEstateDevelopmentsModel = app(config('hub.model_user_company_real_estate_developments'));
        Schema::dropIfExists($userCompanyRealEstateDevelopmentsModel->getTable());
    }
};
