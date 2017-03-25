<?php

namespace Caronae\Http\Controllers;

use Illuminate\Http\Request;
use Mail;

use Caronae\Http\Requests;
use Caronae\Http\Controllers\Controller;
use Caronae\Models\User;

class FalaeController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.v1.auth');
    }

    public function sendMessage(Request $request)
    {
        $user = $request->currentUser;
        $subject = $request->subject;
        $body = $request->message . "\nID UFRJ: " . $user->id_ufrj;

        Mail::raw($body, function ($message) use ($user, $subject) {
            $message->to('caronae@fundoverde.ufrj.br');
            $message->from($user->email, $user->name);
            $message->replyTo($user->email, $user->name);
            $message->subject($subject);
        });
        
        return response()->json(['status' => 'Message sent.']);
    }
}
