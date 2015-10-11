<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRideColumnsNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->renameColumn('zone', 'myzone');
            $table->renameColumn('go', 'going');
            $table->renameColumn('way', 'route');
            $table->renameColumn('time', 'mytime');
            $table->renameColumn('date', 'mydate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->renameColumn('myzone', 'zone');
            $table->renameColumn('going', 'go');
            $table->renameColumn('route', 'way');
            $table->renameColumn('mytime', 'time');
            $table->renameColumn('mydate', 'date');
        });
    }
}
