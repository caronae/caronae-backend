<?php
namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NeighborhoodController extends CrudController
{
    public function setup() {
        $this->crud->setModel('Caronae\Models\Neighborhood');
        $this->crud->setRoute('admin/neighborhoods');
        $this->crud->setEntityNameStrings('bairro', 'bairros');

        $this->crud->setColumns([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'zone', 'label' => 'Zona' ],
        ]);

        $this->crud->addFields([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'zone', 'label' => 'Zona' ],
        ]);
    }

    public function store()
    {
        $this->clearCache();
        return parent::storeCrud();
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'zone' => 'required|string',
        ]);

        $this->clearCache();
        return parent::updateCrud();
    }

    private function clearCache()
    {
        Log::info('Clearing zones cache.');
        Cache::forget('zones');
    }
}