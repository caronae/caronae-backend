<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AssociateHubsWithCampi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hubs', function (Blueprint $table) {
            $table->integer('campus_id')->unsigned()->nullable();
            $table->foreign('campus_id')->references('id')->on('campi');
            $table->dropColumn('campus');
        });

        DB::connection()->getPdo()->exec("
            UPDATE hubs
            SET campus_id = 
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hubs', function (Blueprint $table) {
            $table->dropColumn('campus_id');
            $table->string('campus');
        });
    }
}
