<?php

namespace Caronae\Console\Commands;

use Caronae\Notifications\UpdateAppNotification;
use Caronae\Services\UserAppService;
use DB;
use Illuminate\Console\Command;
use Log;

class NotifyUsersWithOldApp extends Command
{
    protected $signature = 'user:notify-old-app';
    protected $description = 'Notify users that are using an old version to upgrade';

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

        Log::info("Enviando aviso de app desatualizado para {$users->count()} usuários");
        $users->each->notify(new UpdateAppNotification());
    }

}
