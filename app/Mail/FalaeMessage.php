<?php

namespace Caronae\Mail;

use Caronae\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FalaeMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $userMessage;
    public $userSubject;

    public function __construct(User $user, $subject, $message)
    {
        $this->user = $user;
        $this->userSubject = $subject;
        $this->userMessage = $message;
    }

    public function build()
    {
        return $this->to('caronae@fundoverde.ufrj.br')
                    ->replyTo($this->user->email)
                    ->subject($this->userSubject)
                    ->text('emails.falae');
    }
}
