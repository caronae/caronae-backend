<?php

namespace Caronae\Http\Controllers;

use Caronae\Models\Hub;
use Caronae\Models\Neighborhood;

class PlaceController extends Controller
{
	public function index()
	{
		return [
			'neighborhoods' => Neighborhood::all(),
			'hubs' => Hub::all()
		];
	}
}