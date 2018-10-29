<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->increments('id');
            $table->string('zone');
            $table->string('neighborhood');
            $table->boolean('go');
            $table->string('place')->nullable();
            $table->string('way')->nullable();
            $table->integer('routine_id')->nullable();
            $table->string('hub')->nullable();
            $table->integer('slots');
            $table->time('time');
            $table->date('date');
            $table->string('description')->nullable();
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
        Schema::drop('rides');
    }
}
