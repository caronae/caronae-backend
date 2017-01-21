<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TokenUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->fixUsersWithoutToken();
        Schema::table('users', function (Blueprint $table) {
            $table->unique('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropUnique('users_token_unique');
        });
    }

    private function fixUsersWithoutToken()
    {
        // Add a random token to each user with an empty token
        DB::connection()->getPdo()->exec("
            UPDATE users
            SET token = upper(substring(md5(id || name || id_ufrj), 1, 6))
            WHERE token = ''
        ");
    }
}
