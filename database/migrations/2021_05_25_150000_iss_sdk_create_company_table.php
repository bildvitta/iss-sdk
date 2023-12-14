<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IssSdkCreateCompanyTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('hub_companies')) {
            Schema::create('hub_companies', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid');
                $table->string('name');
                $table->softDeletes();
                $table->timestamps();
            });
        }

        $userModel = app(config('hub.model_user'));

        if (! Schema::hasColumn($userModel->getTable(), 'company_id')) {
            Schema::table($userModel->getTable(), function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable();

                $table->foreign('company_id')->references('id')->on('hub_companies');
            });
        }
    }

    public function down()
    {
        $userModel = app(config('hub.model_user'));
        if (Schema::hasColumn($userModel->getTable(), 'company_id')) {
            Schema::table($userModel->getTable(), function (Blueprint $table) {
                $table->dropForeign('company_id');
                $table->dropColumn('company_id');
            });
        }

        Schema::dropIfExists('hub_companies');
    }
}
