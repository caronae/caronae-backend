<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RideStatusEnum extends Migration
{
    /**
     * Change type of 'status' column from 'ride_user' (INT from 0 to 4) to VARCHAR.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ride_user', function (Blueprint $table) {
            $table->renameColumn('status', 'status_int');
            $table->string('status_string', 15);
        });

        DB::table('ride_user')->where('status_int', 0)->update(['status_string' => 'driver']);
        DB::table('ride_user')->where('status_int', 1)->update(['status_string' => 'pending']);
        DB::table('ride_user')->where('status_int', 2)->update(['status_string' => 'accepted']);
        DB::table('ride_user')->where('status_int', 3)->update(['status_string' => 'refused']);
        DB::table('ride_user')->where('status_int', 4)->update(['status_string' => 'quit']);

        Schema::table('ride_user', function (Blueprint $table) {
            $table->renameColumn('status_string', 'status');
            $table->dropColumn('status_int');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ride_user', function (Blueprint $table) {
            $table->renameColumn('status', 'status_string');
            $table->integer('status_int')->default(-1);
        });

        DB::table('ride_user')->where('status_string', 'driver')->update(['status_int' => 0]);
        DB::table('ride_user')->where('status_string', 'pending')->update(['status_int' => 1]);
        DB::table('ride_user')->where('status_string', 'accepted')->update(['status_int' => 2]);
        DB::table('ride_user')->where('status_string', 'refused')->update(['status_int' => 3]);
        DB::table('ride_user')->where('status_string', 'quit')->update(['status_int' => 4]);

        Schema::table('ride_user', function (Blueprint $table) {
            $table->renameColumn('status_int', 'status');
            $table->dropColumn('status_string');
        });
    }
}
