<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveFeedbackColumn extends Migration
{
    public function up()
    {
        Schema::table('ride_user', function (Blueprint $table) {
            $table->dropColumn('feedback');
        });
    }

    public function down()
    {
        Schema::table('ride_user', function (Blueprint $table) {
            $table->string('feedback')->nullable();
        });
    }
}
