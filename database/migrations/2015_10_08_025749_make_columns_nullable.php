<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeColumnsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->string('profile')->nullable()->change();
            $table->string('course')->nullable()->change();
            $table->string('phoneNumber')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->boolean('carOwner')->nullable()->change();
            $table->string('carColor')->nullable()->change();
            $table->string('carPlate')->nullable()->change();
            $table->dropColumn('unit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->string('unit')->nullable();
        });
    }
}
