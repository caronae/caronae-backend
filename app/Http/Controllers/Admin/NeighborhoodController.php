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
            [ 'name' => 'distance', 'label' => 'Distância', 'type' => 'number', 'suffix' => 'km' ],
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Adding neighborhood ' . $request->name);
        $this->clearCache();
        return parent::storeCrud();
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'zone' => 'required|string',
        ]);

        Log::info('Updating neighborhood ' . $request->name);
        $this->clearCache();
        return parent::updateCrud();
    }

    public function destroy($id)
    {
        Log::info('Deleting neighborhood ' . $id);
        $this->clearCache();
        return parent::destroy($id);
    }

    private function clearCache()
    {
        Log::info('Clearing zones cache.');
        Cache::forget('zones');
    }
}