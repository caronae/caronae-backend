<?php

namespace Caronae\Console\Commands;

use Caronae\Models\User;
use Caronae\Notifications\UpdateAppNotification;
use DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise;
use Illuminate\Console\Command;
use Log;

class NotifyUsersWithOldApp extends Command
{
    protected $signature = 'user:notify-old-app';
    protected $description = 'Notify users that are using an old version to upgrade';

    public function handle()
    {
        $users = User::where('email', 'macecchi@gmail.com')->get();

        Log::info("Enviando aviso de app desatualizado para {$users->count()} usuários");
        $users->each->notify(new UpdateAppNotification());
    }

}
