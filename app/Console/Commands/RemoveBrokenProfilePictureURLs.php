<?php

namespace Caronae\Console\Commands;

use Caronae\Models\User;
use DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise;
use Illuminate\Console\Command;
use Log;

class RemoveBrokenProfilePictureURLs extends Command
{
    protected $signature = 'content:remove-broken-images';
    protected $description = 'Remove profile pictures with broken URLs from the database';

    private const REQUEST_CONCURRENCY_LIMIT = 5;
    private const REQUEST_TIMEOUT_LIMIT = 15.0;
    private $totalUsersWithInvalidImages;
    private $totalProcessed = 0;
    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'timeout' => self::REQUEST_TIMEOUT_LIMIT,
        ]);
        $this->totalUsersWithInvalidImages = collect();
    }

    public function handle()
    {
        $this->gatherUsersWithBlankProfilePicture();
        $this->gatherUsersWithBrokenProfilePictures();

        $this->updateUsers();

        Log::info("Análise concluída. $this->totalProcessed usuários processados. {$this->totalUsersWithInvalidImages->count()} usuários com imagens inválidas identificados.");
    }

    private function gatherUsersWithBlankProfilePicture()
    {
        $users = User::where('profile_pic_url', '')->get(['id']);
        Log::info("Usuários com imagem em branco: {$users->count()}");

        $this->totalUsersWithInvalidImages = $this->totalUsersWithInvalidImages->concat($users);
        $this->totalProcessed += $users->count();
    }

    private function gatherUsersWithBrokenProfilePictures()
    {
        $totalProcessed = 0;
        $query = User::whereNotNull('profile_pic_url')->where('profile_pic_url', 'NOT LIKE', 'https://sigadocker.ufrj.br%');
        $totalUsers = $query->count();

        $query->chunk(1000, function ($users) use ($totalUsers, &$totalProcessed) {
            Log::info("Analisando imagens de {$users->count()} usuários ($totalProcessed/$totalUsers)");

            $usersWithInvalidImages = $this->filterUsersWithBrokenProfilePictures($users);
            $this->totalUsersWithInvalidImages = $this->totalUsersWithInvalidImages->concat($usersWithInvalidImages);
            Log::debug("{$usersWithInvalidImages->count()} usuários possuem imagens inválidas");

            $totalProcessed += $users->count();
        });

        $this->totalProcessed += $totalProcessed;
    }

    private function filterUsersWithBrokenProfilePictures($users)
    {
        $invalidUsers = collect();

        $requests = $users->map(function ($user) use ($invalidUsers) {
            $profile_pic_url = $user->profile_pic_url;
            return $this->client->getAsync($profile_pic_url)->then(null, function (GuzzleException $exception) use ($user, $invalidUsers) {
                Log::debug("Imagem inválida: {$user->profile_pic_url}", [$exception->getMessage()]);
                $invalidUsers[] = $user;
            });
        });

        Promise\each_limit($requests->toArray(), self::REQUEST_CONCURRENCY_LIMIT)->wait(true);

        return $invalidUsers;
    }

    private function updateUsers()
    {
        Log::debug("Atualizando {$this->totalUsersWithInvalidImages->count()} usuários");
        User::whereIn('id', $this->totalUsersWithInvalidImages->pluck('id'))->update([
            'profile_pic_url_old' => DB::raw('profile_pic_url'),
            'profile_pic_url' => null,
        ]);
    }
}
