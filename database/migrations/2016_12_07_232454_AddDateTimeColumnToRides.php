<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateTimeColumnToRides extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dateTime('date')->after('slots')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index(['date', 'done']);
        });

        Caronae\Models\Ride::withTrashed()->update([
        	'date' => DB::raw('mydate + mytime')
        ]);

        Schema::table('rides', function (Blueprint $table) {
        	$table->dropColumn('mydate');
        	$table->dropColumn('mytime');
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
        	$table->date('mydate')->after('slots')->default(DB::raw('CURRENT_TIMESTAMP'));
        	$table->time('mytime')->after('mydate')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

    	Caronae\Models\Ride::withTrashed()->update([
        	'mydate' => DB::raw('date::DATE'),
        	'mytime' => DB::raw('date::TIME')
        ]);

        Schema::table('rides', function (Blueprint $table) {
            $table->dropIndex(['date', 'done']);
            $table->dropColumn('date');
        });
    }
}
