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
        $userCompanyModel = app(config('hub.model_user_company'));
        $userCompanyParentPositionModel = app(config('hub.model_user_company_parent_position'));

        Schema::create($userCompanyParentPositionModel->getTable(), function (Blueprint $table) use ($userCompanyModel) {
            $table->id();
            $table->foreignId('user_company_id')->constrained($userCompanyModel->getTable())->cascadeOnDelete();
            $table->foreignId('user_company_parent_id')->constrained($userCompanyModel->getTable())->cascadeOnDelete();
            $table->softDeletes();
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
        $userCompanyParentPositionModel = app(config('hub.model_user_company_parent_position'));
        Schema::dropIfExists($userCompanyParentPositionModel->getTable());
    }
};
