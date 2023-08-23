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
        $userModel = app(config('hub.model_user'));
        $positionModel = app(config('hub.model_position'));
        $companyModel = app(config('hub.model_company'));

        Schema::create($userCompanyModel->getTable(), function (Blueprint $table) use ($userModel, $positionModel, $companyModel) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('position_id')->nullable();
            $table->boolean('is_seller')->default(false);
            $table->boolean('has_all_real_estate_developments')->default(false);
            $table->boolean('has_specific_permissions')->default(false);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on($userModel->getTable())->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on($companyModel->getTable())->onDelete('cascade');
            $table->foreign('position_id')->references('id')->on($positionModel->getTable())->onDelete('cascade');
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
        $userCompanyModel = app(config('hub.model_user_company'));
        Schema::dropIfExists($userCompanyModel->getTable());
    }
};
