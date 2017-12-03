<?php

namespace Caronae\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Ride extends Resource
{
    public function toArray($request)
    {
        $driver = new User($this->driver());
        return [
            'id' => $this->id,
            'myzone' => $this->myzone,
            'neighborhood' => $this->neighborhood,
            'going' => $this->going,
            'place' => $this->place,
            'route' => $this->route,
            'routine_id' => $this->routine_id,
            'hub' => $this->hub,
            'slots' => $this->slots,
            'mytime' => $this->date->format('H:i:s'),
            'mydate' => $this->date->format('Y-m-d'),
            'description' => $this->description,
            'week_days' => $this->week_days,
            'repeats_until' => $this->repeats_until,
            'driver' => $driver->toArray($request),
            'riders' => User::collection($this->whenLoaded('riders')),
        ];
    }
}
