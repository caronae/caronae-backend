<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlugAndMessageToInstitutions extends Migration
{
    public function up()
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('slug')->nullable();
            $table->text('login_message')->nullable();
        });

        DB::connection()->getPdo()->exec("
            UPDATE institutions SET slug = LOWER(name)
        ");

        Schema::table('institutions', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down()
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('login_message');
        });
    }
}
