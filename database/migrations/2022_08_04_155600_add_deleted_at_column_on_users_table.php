<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtColumnOnUsersTable extends Migration
{
    public function up()
    {
        $userModel = app(config('hub.model_user'));
        if (!Schema::hasColumn($userModel->getTable(), 'deleted_at')) {
            Schema::table($userModel->getTable(), function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        $userModel = app(config('hub.model_user'));
        if (Schema::hasColumn($userModel->getTable(), 'deleted_at')) {
            Schema::table($userModel->getTable(), function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
}
