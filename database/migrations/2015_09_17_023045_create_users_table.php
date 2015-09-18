<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('profile');
            $table->string('course');
            $table->string('unit');
            $table->string('zone');
            $table->string('neighborhood');
            $table->string('phoneNumber');
            $table->string('email');
            $table->boolean('carOwner');
            $table->string('carModel');
            $table->string('carColor');
            $table->string('carPlate');
            $table->rememberToken();
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::drop('users');
    }
}