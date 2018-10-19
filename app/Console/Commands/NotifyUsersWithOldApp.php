<?php

namespace Caronae\Console\Commands;

use Carbon\Carbon;
use Caronae\Models\User;
use Caronae\Notifications\UpdateAppNotification;
use Caronae\Services\UserAppService;
use DB;
use Illuminate\Console\Command;
use Log;

class NotifyUsersWithOldApp extends Command
{
    protected $signature = 'user:notify-old-app';
    protected $description = 'Notify users that are using an old version to update';

    /**
     * @var UserAppService
     */
    private $appService;

    public function __construct(UserAppService $appService)
    {
        parent::__construct();
        $this->appService = $appService;
    }

    public function handle()
    {
        $users = $this->appService->getActiveUsersWithOldAppVersions();
        $users = $this->excludeUsersThatWereAlreadyNotified($users);

        Log::info("Enviando aviso de app desatualizado para {$users->count()} usuários");
        $users->each->notify(new UpdateAppNotification());
    }

    private function excludeUsersThatWereAlreadyNotified($users)
    {
        return $users->reject(function (User $user) {
            return $user->notifications()
                ->where('type', UpdateAppNotification::class)
                ->whereDate('created_at', '>', new Carbon('15 days ago'))
                ->exists();
        });
    }

}
