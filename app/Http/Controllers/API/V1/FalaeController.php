<?php

namespace Caronae\Http\Controllers\API\v1;

use Caronae\Http\Controllers\BaseController;
use Caronae\Mail\FalaeMessage;
use Illuminate\Http\Request;
use Log;
use Mail;

class FalaeController extends BaseController
{
    public function sendMessage(Request $request)
    {
        $user = $request->user();
        $subject = $request->subject;
        $body = $request->message;

        Log::info('Enviando mensagem através do Falaê', ['user_id' => $user->id, 'subject' => $subject]);

        Mail::send(new FalaeMessage($user, $subject, $body));

        return response()->json(['status' => 'Message sent.']);
    }
}
