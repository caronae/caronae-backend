<?php

namespace Caronae\Http\Controllers;

use Illuminate\Http\Request;
use Caronae\Models\Hub;
use Caronae\Models\Neighborhood;

class PlaceController extends Controller
{
	public function index(Request $request)
	{
		return [
			'neighbodhoods' => Neighborhood::all(),
			'hubs' => Hub::all()
		];
	}
}