<?php

use Caronae\Models\Message;
use Illuminate\Database\Migrations\Migration;

class EncryptMessages extends Migration
{
    public function up()
    {
        Message::all()->each(function ($message) {
            $message->body = $message->getAttributeValue('body');
            $message->save();
        });
    }

    public function down()
    {
        Message::all()->each(function ($message) {
            DB::connection()->getPdo()->exec("
                UPDATE messages SET body = '" . $message->body . "' WHERE id = " . $message->id . '
            ');
        });
    }
}
