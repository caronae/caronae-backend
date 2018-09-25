<?php

namespace Caronae\Console\Commands;

use Caronae\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\Command;
use Log;

class RemoveBrokenProfilePictureURLs extends Command
{
    protected $signature = 'content:remove-broken-images';
    protected $description = 'Remove profile pictures with broken URLs from the database';
    private $client;

    private const REQUEST_CONCURRENCY_LIMIT = 15;
    private const REQUEST_TIMEOUT_LIMIT = 15.0;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'timeout' => self::REQUEST_TIMEOUT_LIMIT,
        ]);
    }

    public function handle()
    {
        $query = User::whereNotNull('profile_pic_url');
        $totalUsers = $query->count();
        $totalProcessed = 0;
        $totalUsersWithInvalidImages = collect();

        $query->chunk(1000, function ($users) use ($totalUsers, &$totalProcessed, &$totalUsersWithInvalidImages) {
            Log::info("Analisando imagens de {$users->count()} usuários ($totalProcessed/$totalUsers)");

            $usersWithInvalidImages = $this->usersWithInvalidProfilePictures($users);
            $totalUsersWithInvalidImages = $totalUsersWithInvalidImages->concat($usersWithInvalidImages);
            Log::debug("{$usersWithInvalidImages->count()} usuários possuem imagens inválidas");

            $totalProcessed += $users->count();
        });

        Log::debug("Atualizando {$totalUsersWithInvalidImages->count()} usuários");
        User::whereIn('id', $totalUsersWithInvalidImages->pluck('id'))->update(['profile_pic_url' => null]);

        Log::info("Análise concluída. $totalProcessed usuários processados.");
    }

    private function usersWithInvalidProfilePictures($users)
    {
        $invalidUsers = collect();

        $requests = $users->map(function ($user) use ($invalidUsers) {
            $profile_pic_url = $user->profile_pic_url;
            return $this->client->getAsync($profile_pic_url)->then(null, function () use ($user, $invalidUsers) {
                $invalidUsers[] = $user;
            });
        });

        Promise\each_limit($requests->toArray(), self::REQUEST_CONCURRENCY_LIMIT)->wait(true);

        return $invalidUsers;
    }
}
