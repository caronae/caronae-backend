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
        return ['mail'];
    }

    public function toMail(User $user)
    {
        return (new MailMessage)
                    ->subject('Atualize seu app do Caronaê')
                    ->greeting("Oi, {$user->firstName}!")
                    ->line('Parece que você está usando uma versão antiga do app do Caronaê.')
                    ->line('Para continuarmos desenvolvendo melhorias no app, a equipe do Caronaê oferece suporte apenas às versões mais recentes lançadas.')
                    ->line('As nossas atualizações do app também sempre possuem correções de estabilidade e novas funcionalidades, então você só sai ganhando!')
                    ->line('Atualize o app pela App Store ou Google Play para continuar usando o Caronaê.');
    }
}
