<?php

namespace Caronae\Http\Controllers\API\v1;

use Caronae\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Log;
use Mail;

class FalaeController extends BaseController
{
    public function __construct()
    {
        $this->middleware('api.v1.auth');
    }

    public function sendMessage(Request $request)
    {
        $user = $request->user();
        $subject = $request->subject;
        $body = $request->message . "\nID UFRJ: " . $user->id_ufrj;

        Log::info('Enviando mensagem através do Falaê', ['user_id' => $user->id, 'user_email' => $user->email, 'subject' => $subject, 'message' => $body]);

        Mail::raw($body, function ($message) use ($user, $subject) {
            $message->to('caronae@fundoverde.ufrj.br');
            $message->replyTo($user->email, $user->name);
            $message->subject($subject);
        });
        
        return response()->json(['status' => 'Message sent.']);
    }
}
