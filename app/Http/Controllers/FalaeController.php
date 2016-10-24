<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

class FalaeController extends Controller
{
    public function sendMessage(Request $request)
    {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
        if ($user == null) {
            return response()->json(['error'=>'User token not authorized.'], 403);
        }

        $to = "caronae@fundoverde.ufrj.br";
        $headers = [];
        $headers[] = "Content-type: text/plain; charset=utf-8";
        $headers[] = "From: {$user->name} <{$user->email}>";

        $subject = $decode->subject;
        $message = $decode->message . "\nID UFRJ: " . $user->id_ufrj;
        $headers[] = "Subject: {$subject}";

        $mailStatus = mail($to, $subject, $message, implode("\r\n", $headers));
        if ($mailStatus) {
            return response()->json(['status'=>'Message sent.', 'headers'=>$headers]);
        } else {
            return response()->json(['status'=>'Message failed to send.'], 500);
        }
    }
}
