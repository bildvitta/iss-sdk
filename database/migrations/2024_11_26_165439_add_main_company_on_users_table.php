<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $userModel = app(config('hub.model_user'));
        $companyModel = app(config('hub.model_company'));

        Schema::table($userModel->getTable(), function (Blueprint $table) use ($userModel) {
            if (! Schema::hasColumn($userModel->getTable(), 'main_company_id')) {
                $table->after('company_id', function ($table) {
                    $table->foreignId('main_company_id')
                        ->nullable()
                        ->constrained($companyModel->getTable())
                        ->onDelete('set null');
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $userModel = app(config('hub.model_user'));

        Schema::table($userModel->getTable(), function (Blueprint $table) {
            $table->dropForeign(['main_company_id']);
            $table->dropColumn('main_company_id');
        });
    }
};
