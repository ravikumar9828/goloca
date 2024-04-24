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
        Schema::create('user_models', function (Blueprint $table) {
            $table->id();
            $table->string('first_name'); 
            $table->string('last_name'); 
            $table->string('email')->unique();  
            $table->string('password');
            $table->string('plain_password');
            $table->string('pro_img')->nullable();
            $table->string('country');
            $table->string('phone')->nullable();
            $table->string('terms');
            $table->longText('is_block')->nullable()->default('text');
            $table->string('status');
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
        Schema::dropIfExists('user_models');
    }
};
