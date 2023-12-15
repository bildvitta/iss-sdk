<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsSuperUserOnUsers extends Migration
{
    public function up()
    {
        $userModel = app(config('hub.model_user'));
        if (! Schema::hasColumn($userModel->getTable(), 'is_superuser')) {
            Schema::table($userModel->getTable(), function (Blueprint $table) {
                $table->boolean('is_superuser')->default(false);
            });
        }
    }

    public function down()
    {
        $userModel = app(config('hub.model_user'));
        if (Schema::hasColumn($userModel->getTable(), 'is_superuser')) {
            Schema::table($userModel->getTable(), function (Blueprint $table) {
                $table->removeColumn('is_superuser');
            });
        }
    }
}
