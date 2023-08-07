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
        Schema::create('user_company_parent_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_company_id');
            $table->unsignedBigInteger('user_company_parent_id');
            $table->foreign('user_company_id')->references('id')->on('user_companies')->onDelete('cascade');
            $table->foreign('user_company_parent_id')->references('id')->on('user_companies')->onDelete('cascade');
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
        Schema::dropIfExists('user_company_parent_positions');
    }
};
