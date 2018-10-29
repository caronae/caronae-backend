<?php

namespace Caronae\Http\Controllers\API\v1;

use Caronae\Http\Controllers\BaseController;
use Caronae\Http\Resources\MessageResource;
use Caronae\Models\Message;
use Caronae\Models\Ride;
use Caronae\Notifications\RideMessageReceived;
use Illuminate\Http\Request;

class ChatController extends BaseController
{
    public function getMessages(Request $request, Ride $ride)
    {
        $this->validate($request, [
            'since' => 'date',
        ]);

        if ($request->since) {
            $messages = $ride->messages()->where('created_at', '>', $request->since)->orderBy('created_at')->get();
        } else {
            $messages = $ride->messages()->orderBy('created_at')->get();
        }

        return [
            'messages' => MessageResource::collection($messages),
        ];
    }

    public function sendMessage(Request $request, Ride $ride)
    {
        $this->validate($request, [
            'message' => 'required',
        ]);

        $message = Message::create([
            'ride_id' => $ride->id,
            'user_id' => $request->user()->id,
            'body' => $request->message,
        ]);
        $notification = new RideMessageReceived($message);

        $subscribers = $ride->users()
            ->whereIn('status', ['accepted', 'driver'])
            ->where('user_id', '!=', $request->user()->id)
            ->get();
        $subscribers->each->notify($notification);

        return response()->json([
            'message' => 'Message sent.',
            'id' => $message->id,
        ], 201);
    }
}
