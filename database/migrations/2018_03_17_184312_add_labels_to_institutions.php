<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLabelsToInstitutions extends Migration
{
    public function up()
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('going_label')->default('Chegando')->nullable(false);
            $table->string('leaving_label')->default('Saindo')->nullable(false);
        });
    }

    public function down()
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['going_label', 'leaving_label']);
        });
    }
}
