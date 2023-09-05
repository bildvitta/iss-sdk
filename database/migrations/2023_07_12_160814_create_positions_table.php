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
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained($companyModel->getTable())->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('parent_position_id')->constrained($positionModel->getTable())->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'company_id']);
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
