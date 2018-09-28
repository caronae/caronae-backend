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

    public function __construct(User $user, $subject, $message)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->userMessage = $message;
    }

    public function build()
    {
        return $this->to('caronae@fundoverde.ufrj.br')
                    ->from($this->user->email, $this->user->name)
                    ->replyTo($this->user->email)
                    ->text('emails.falae');
    }
}
