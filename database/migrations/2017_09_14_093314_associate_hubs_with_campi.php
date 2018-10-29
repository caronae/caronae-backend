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
        });

        DB::connection()->getPdo()->exec('
            UPDATE hubs
            SET campus_id = (SELECT id FROM campi WHERE campi.name = hubs.campus)
        ');

        Schema::table('hubs', function (Blueprint $table) {
            $table->integer('campus_id')->nullable(false)->change();
            $table->dropColumn('campus');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hubs', function (Blueprint $table) {
            $table->string('campus')->nullable();
        });

        DB::connection()->getPdo()->exec('
            UPDATE hubs
            SET campus = (SELECT campi.name FROM campi WHERE campi.id = hubs.campus_id)
        ');

        Schema::table('hubs', function (Blueprint $table) {
            $table->dropColumn('campus_id');
            $table->string('campus')->nullable(false)->change();
        });
    }
}
