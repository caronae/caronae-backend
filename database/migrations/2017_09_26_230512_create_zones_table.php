<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZonesTable extends Migration
{
    public function up()
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('color', 7)->nullable();
        });

        $this->initializeZones();

        $this->createZoneAssociations();
    }

    public function down()
    {
        $this->undoZoneAssociations();

        Schema::dropIfExists('zones');
    }

    private function initializeZones()
    {
        DB::connection()->getPdo()->exec("
            INSERT INTO zones (name)
            SELECT DISTINCT zone FROM neighborhoods
        ");
    }

    private function createZoneAssociations()
    {
        Schema::table('neighborhoods', function (Blueprint $table) {
            $table->integer('zone_id')->unsigned()->nullable();
            $table->foreign('zone_id')->references('id')->on('zones');
        });

        DB::connection()->getPdo()->exec("
            UPDATE neighborhoods
            SET zone_id = (SELECT id FROM zones WHERE zones.name = neighborhoods.zone)
        ");

        Schema::table('neighborhoods', function (Blueprint $table) {
            $table->integer('zone_id')->nullable(false)->change();
            $table->dropColumn('zone');
        });
    }

    private function undoZoneAssociations()
    {
        Schema::table('neighborhoods', function (Blueprint $table) {
            $table->string('zone')->nullable();
        });

        DB::connection()->getPdo()->exec("
            UPDATE neighborhoods
            SET zone = (SELECT zones.name FROM zones WHERE zones.id = neighborhoods.zone_id)
        ");

        Schema::table('neighborhoods', function (Blueprint $table) {
            $table->dropColumn('zone_id');
            $table->string('zone')->nullable(false)->change();
        });
    }
}
