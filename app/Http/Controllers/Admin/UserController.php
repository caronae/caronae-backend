<?php

namespace Caronae\Http\Controllers\Admin;

use Caronae\Http\Controllers\Controller;
use Caronae\ExcelExport\ExcelExporter;
use Caronae\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return view('users.index')->with('banned', !$request->has('banned'));
    }

    public function indexJson(Request $request)
    {
        if ($request->has('banned')) {
            return User::onlyTrashed()->get();
        } else {
            return User::all();
        }
    }

    public function indexExcel(Request $request)
    {
        $query = User::select('name', 'email', 'profile', 'course', 'location');

        if ($request->has('banned')) {
            $query = $query->onlyTrashed();
        }

        $data = $query->get()->toArray();

        (new ExcelExporter())->export('usuarios', [
            'Nome', 'Email', 'Perfil UFRJ', 'Curso', 'Bairro'
        ], $data, $request->get('type', 'xlsx'));
    }

    public function banish($id)
    {
        $user = User::find($id);

        $user->banish();

        return back()->with('message', 'Usuario "'.$user->name.'" banido com sucesso.');
    }

    public function unban($id)
    {
        $user = User::withTrashed()->find($id);

        $user->unban();

        return back()->with('message', 'Usuario "'.$user->name.'" desbanido com sucesso.');
    }

}
