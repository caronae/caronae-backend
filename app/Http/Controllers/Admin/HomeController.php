<?php

namespace Caronae\Http\Controllers\Admin;

use Backpack\Base\app\Http\Controllers\Controller as Controller;

class HomeController extends Controller
{
    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function dashboard()
    {
        $this->data['title'] = 'Painel';

        return view('home.index', $this->data);
    }
}
