<?php 
namespace Caronae\Http\Controllers\Admin2;

use Backpack\Base\app\Http\Controllers\Controller as Controller;
use Caronae\Models\Ride;
use Caronae\Http\Requests\RankingRequest;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $this->data['title'] = 'Painel';
        return view('home.index', $this->data);
    }
}