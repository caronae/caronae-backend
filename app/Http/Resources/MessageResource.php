<?php

namespace Caronae\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MessageResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'user' => new UserResource($this->user),
            'date' => $this->date->toDateTimeString(),
        ];
    }
}
