<?php
namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HubController extends CrudController
{
    public function setup() {
        $this->crud->setModel('Caronae\Models\Hub');
        $this->crud->setRoute('admin/hubs');
        $this->crud->setEntityNameStrings('hub', 'hubs');
        $this->crud->setDefaultPageLength(100);

        $this->crud->setColumns([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'center', 'label' => 'Centro' ],
            [ 'name' => 'campus', 'label' => 'Campus' ],
        ]);

        $this->crud->addFields([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'center', 'label' => 'Centro' ],
            [ 'name' => 'campus', 'label' => 'Campus' ],
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Adding hub ' . $request->name);
        $this->clearCache();
        return parent::storeCrud();
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'center' => 'required|string',
            'campus' => 'required|string',
        ]);

        Log::info('Updating hub ' . $request->name);
        $this->clearCache();
        return parent::updateCrud();
    }

    public function destroy($id)
    {
        Log::info('Deleting hub ' . $id);
        $this->clearCache();
        return parent::destroy($id);
    }

    private function clearCache()
    {
        Log::info('Clearing campi cache.');
        Cache::forget('campi');
    }
}