<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

class FalaeController extends Controller
{
    public function sendMessage(Request $request) {
        $decode = json_decode($request->getContent());
		$user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		if ($user->deleted_at != null) {
			return response()->json(['error'=>'User banned.'], 403);
		}
		
		$to = "macecchi@gmail.com";
		$headers = [];
		$headers[] = "Content-type: text/plain; charset=utf-8";
		$headers[] = "From: {$user->name} <{$user->email}>";
		$headers[] = "Subject: {$decode->subject}";

		$mailStatus = mail($to, $decode->subject, $decode->message, implode("\r\n", $headers));

		if ($mailStatus) {
			return response()->json(['status'=>'Message sent.', 'headers'=>$headers]);
		} else {
			return response()->json(['status'=>'Message failed to send.'], 500);
		}
    }
	
}
