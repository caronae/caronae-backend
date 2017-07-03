<?php 
namespace Caronae\Http\Controllers\Admin;

use Backpack\Base\app\Http\Controllers\Controller as Controller;

class HomeController extends Controller
{
    public function index()
    {
        $this->data['title'] = 'Painel';
        return view('home.index', $this->data);
    }
}