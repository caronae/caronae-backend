<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtensionUnaccent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::connection()->getDriverName() != 'pgsql') return;

        DB::connection()->getPdo()->exec('
            CREATE EXTENSION IF NOT EXISTS unaccent;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::connection()->getDriverName() != 'pgsql') return;

        DB::connection()->getPdo()->exec('
            DROP EXTENSION IF EXISTS unaccent;
        ');
    }
}
