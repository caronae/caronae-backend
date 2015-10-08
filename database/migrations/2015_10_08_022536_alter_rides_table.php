<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rides', function ($table) {
            $table->boolean('go')->nullable();
            $table->string('place')->nullable();
            $table->string('way')->nullable();
            $table->integer('routine_id')->nullable();
            $table->dropColumn('street');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rides', function ($table) {
            $table->dropColumn('go');
            $table->dropColumn('place');
            $table->dropColumn('way');
            $table->dropColumn('routine_id');
            $table->string('street')->nullable();
        });
    }
}
