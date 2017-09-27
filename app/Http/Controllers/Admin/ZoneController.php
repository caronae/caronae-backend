<?php
namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZoneController extends CrudController
{
    public function setup() {
        $this->crud->setModel('Caronae\Models\Zone');
        $this->crud->setRoute('admin/zones');
        $this->crud->setEntityNameStrings('zona', 'zonas');
        $this->crud->denyAccess(['delete']);

        $this->crud->setColumns([
            [ 'name' => 'name', 'label' => 'Nome', 'type' => 'zone' ],
            [ 'name' => 'neighborhoods', 'label' => 'Bairros', 'type' => 'neighborhoodList' ],
        ]);

        $this->crud->addFields([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'color', 'label' => 'Color' ],
        ]);
    }

    public function store(Request $request)
    {
        $this->validateInput($request);

        Log::info('Adding zone ' . $request->name);
        $this->clearCache();
        return parent::storeCrud();
    }

    public function update(Request $request)
    {
        $this->validateInput($request);

        Log::info('Updating zone ' . $request->name);
        $this->clearCache();
        return parent::updateCrud();
    }

    public function destroy($id)
    {
        Log::info('Deleting zone ' . $id);
        $this->clearCache();
        return parent::destroy($id);
    }

    private function clearCache()
    {
        Log::info('Clearing zones cache.');
        Cache::forget('zones');
    }

    private function validateInput(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
        ]);
    }
}