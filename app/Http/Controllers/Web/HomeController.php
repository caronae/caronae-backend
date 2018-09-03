<?php

namespace Caronae\Http\Controllers\Web;

use Caronae\Http\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        return view('home.landing');
    }
}
