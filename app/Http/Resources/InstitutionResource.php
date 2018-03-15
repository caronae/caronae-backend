<?php

namespace Caronae\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class InstitutionResource extends Resource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
        ];
    }
}