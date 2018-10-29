<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IdUfrjUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fix to delete users with an empty UFRJ id
        $this->clearInvalidUsers();

        Schema::table('users', function (Blueprint $table) {
            $table->unique('id_ufrj');
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
            $table->dropUnique('users_id_ufrj_unique');
        });
    }

    private function clearInvalidUsers()
    {
        $this->clearUsersWithoutUFRJId();
        $this->mergeUserWithDuplicatedIds();
    }

    private function clearUsersWithoutUFRJId()
    {
        // Delete relationships
        DB::connection()->getPdo()->exec("DELETE 
            FROM ride_user ru
            USING users u
            WHERE ru.user_id = u.id
            AND u.id_ufrj = ''
        ");

        // Delete the users
        DB::connection()->getPdo()->exec("DELETE FROM users WHERE id_ufrj = ''");
    }

    private function mergeUserWithDuplicatedIds()
    {
        // Move all relationships to one user
        DB::connection()->getPdo()->exec('UPDATE ride_user SET user_id = 8540 WHERE user_id IN (8539, 8541)');

        // Delete the duplicated users
        DB::connection()->getPdo()->exec('DELETE FROM users WHERE id IN (8539, 8541)');
    }
}
