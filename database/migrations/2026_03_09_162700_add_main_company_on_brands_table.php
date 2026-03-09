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

        Schema::table($brandModel->getTable(), function (Blueprint $table) use ($brandModel, $companyModel) {
            if (! Schema::hasColumn($brandModel->getTable(), 'main_company_id')) {
                $table->after('name', function ($table) use ($companyModel) {
                    $table->foreignId('main_company_id')
                        ->nullable()
                        ->constrained($companyModel->getTable())
                        ->onDelete('set null');
                });
            }
        });
    }

    public function down(): void
    {
        $brandModel = app(config('hub.model_brand'));
        Schema::table($brandModel->getTable(), function (Blueprint $table) {
            $table->dropForeign(['main_company_id']);
            $table->dropColumn('main_company_id');
        });
    }
};
