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
        $this->userMessage = $message;

        $this->to('caronae@fundoverde.ufrj.br');
        $this->replyTo($this->user->email, $this->user->name);
        $this->subject($subject);
    }

    public function build()
    {
        return $this->text('emails.falae');
    }
}
