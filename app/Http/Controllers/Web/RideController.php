<?php

namespace Caronae\Http\Controllers\Web;

use Caronae\Http\Controllers\BaseController;
use Caronae\Models\Ride;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RideController extends BaseController
{
    public function show($id)
    {
        try {
            $ride = Ride::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->view('rides.notFound')->setStatusCode(404);
        }

        $title = $ride->title . ' | ' . $ride->date->format('H:i');
        $driver = $ride->driver()->name;
        $deepLinkUrl = 'caronae://carona/' . $ride->id;
        return view('rides.showWeb', ['title' => $title, 'driver' => $driver, 'deepLinkUrl' => $deepLinkUrl]);
    }
}
