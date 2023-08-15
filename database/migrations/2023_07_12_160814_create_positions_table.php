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
        $positionModel = app(config('hub.model_position'));
        $companyModel = app(config('hub.model_company'));
        Schema::create($positionModel->getTable(), function (Blueprint $table) use ($positionModel, $companyModel) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_position_id')->nullable();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('company_id')->default('1');
            $table->foreign('company_id')->references('id')->on($companyModel->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('parent_position_id')->references('id')->on($positionModel->getTable())->onDelete('cascade');
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
        $positionModel = app(config('hub.model_position'));
        Schema::dropIfExists($positionModel->getTable());
    }
};
