<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campi', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('institution_id')->unsigned();
            $table->foreign('institution_id')->references('id')->on('institutions');
            $table->string('name');
            $table->string('color', 7)->nullable();
            $table->timestamps();
        });

        DB::connection()->getPdo()->exec("
            INSERT INTO campi (name, institution_id)
            SELECT DISTINCT campus, institutions.id FROM hubs, institutions
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campi');
    }
}
