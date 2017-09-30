<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstitutionToUser extends Migration
{
    public function up()
    {
      Schema::table('users', function (Blueprint $table) {
          $table->integer('institution_id')->unsigned()->nullable();
          $table->foreign('institution_id')->references('id')->on('institutions');
      });

      DB::connection()->getPdo()->exec("
          UPDATE users
          SET institution_id = (SELECT id FROM institutions WHERE institutions.name = 'UFRJ')
      ");

      Schema::table('users', function (Blueprint $table) {
          $table->integer('institution_id')->nullable(false)->change();
      });
    }

    public function down()
    {
      Schema::table('users', function (Blueprint $table) {
          $table->dropColumn('institution_id');
      });
    }
}
