<?php

use Illuminate\Database\Migrations\Migration;

class ReplaceLegacyPhotoUrls extends Migration
{
    private $oldURL = 'https://api.caronae.ufrj.br/user/intranetPhoto/';
    private $newURL = 'https://sigadocker.ufrj.br:8090/';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE users SET profile_pic_url = REPLACE(profile_pic_url, '$this->oldURL', '$this->newURL')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE users SET profile_pic_url = REPLACE(profile_pic_url, '$this->newURL', '$this->oldURL')");
    }
}
