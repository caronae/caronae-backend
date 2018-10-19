<?php

namespace Caronae\Notifications;

use Caronae\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UpdateAppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via()
    {
        return ['mail', 'database'];
    }

    public function toArray(User $user)
    {
        return [
            'userID' => $user->id,
            'app_platform' => $user->app_platform,
            'app_version' => $user->app_version,
        ];
    }

    public function toMail(User $user)
    {
        if ($user->app_platform == 'iOS') {
            $store = 'App Store';
            $storeURL = 'https://itunes.apple.com/us/app/caronae-ufrj/id1078790049';
        } else {
            $store = 'Google Play';
            $storeURL = 'https://play.google.com/store/apps/details?id=br.ufrj.caronae';
        }

        return (new MailMessage)
                    ->subject('Atualize seu app do Caronaê')
                    ->greeting("Oi, {$user->firstName}!")
                    ->line('Parece que você está usando uma **versão antiga** do app do Caronaê.')
                    ->line('Para continuarmos desenvolvendo melhorias no app, a equipe do Caronaê oferece suporte apenas às versões mais recentes lançadas.')
                    ->line('As nossas atualizações do app também sempre possuem correções de estabilidade e novas funcionalidades, então você só sai ganhando!')
                    ->line('**Atualize o app pela ' . $store . '** para continuar usando o Caronaê.')
                    ->action('Caronaê na ' . $store, $storeURL);
    }
}
