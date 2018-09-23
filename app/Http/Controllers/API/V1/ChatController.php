<?php

namespace Caronae\Http\Controllers\API\v1;

use Carbon\Carbon;
use Caronae\Http\Controllers\BaseController;
use Caronae\Http\Requests\CreateRideRequest;
use Caronae\Http\Requests\ValidateDuplicateRequest;
use Caronae\Http\Resources\MessageResource;
use Caronae\Http\Resources\RideResource;
use Caronae\Models\Campus;
use Caronae\Models\Message;
use Caronae\Models\Ride;
use Caronae\Models\RideUser;
use Caronae\Models\User;
use Caronae\Notifications\RideCanceled;
use Caronae\Notifications\RideFinished;
use Caronae\Notifications\RideJoinRequestAnswered;
use Caronae\Notifications\RideJoinRequested;
use Caronae\Notifications\RideMessageReceived;
use Caronae\Notifications\RideUserLeft;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ChatController extends BaseController
{
    public function getMessages(Request $request, Ride $ride)
    {
        $this->validate($request, [
            'since' => 'date'
        ]);

        if ($request->since) {
            $messages = $ride->messages()->where('created_at', '>', $request->since)->orderBy('created_at')->get();
        } else {
            $messages = $ride->messages()->orderBy('created_at')->get();
        }

        return [
            'messages' => MessageResource::collection($messages)
        ];
    }

    public function sendMessage(Request $request, Ride $ride)
    {
        $this->validate($request, [
            'message' => 'required'
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
            'id' => $message->id
        ], 201);
    }

}
