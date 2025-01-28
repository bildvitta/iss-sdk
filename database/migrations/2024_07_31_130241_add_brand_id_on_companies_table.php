<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $brandModel = app(config('hub.model_brand'));
        $companyModel = app(config('hub.model_company'));

        Schema::table($companyModel->getTable(), function (Blueprint $table) use ($brandModel, $companyModel) {
            if (! Schema::hasColumn($companyModel->getTable(), 'main_company_id')) {
                $table->foreignId('main_company_id')->nullable()->constrained($companyModel->getTable());
            }

            $table->after('main_company_id', function ($table) use ($brandModel) {
                $table->foreignId('brand_id')->nullable()->constrained($brandModel->getTable());
            });
        });
    }

    public function down(): void
    {
        $companyModel = app(config('hub.model_company'));
        Schema::table($companyModel->getTable(), function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
        });
    }
};
