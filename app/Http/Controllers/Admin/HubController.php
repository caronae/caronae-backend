<?php
namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Caronae\Models\Campus;
use Caronae\Models\Hub;
use Caronae\Models\Institution;
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
            [
                'label' => 'Campus',
                'type' => 'select',
                'name' => 'campus_id',
                'entity' => 'campus',
                'attribute' => 'fullName',
                'model' => 'Caronae\Models\Campus',
            ],
        ]);

        $this->crud->addFields([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'center', 'label' => 'Centro' ],
            [
                'label' => 'Campus',
                'type' => 'select2',
                'name' => 'campus_id',
                'entity' => 'campus',
                'attribute' => 'fullName',
                'model' => 'Caronae\Models\Campus',
            ]
        ]);
    }

    public function store(Request $request)
    {
        $this->validateHub($request);

        Log::info('Adding hub ' . $request->name);
        $this->clearCache($request);
        return parent::storeCrud($request);
    }

    public function update(Request $request)
    {
        $this->validateHub($request);

        Log::info('Updating hub ' . $request->name);
        $this->clearCache($request);
        return parent::updateCrud($request);
    }

    public function destroy($id)
    {
        $hub = Hub::find($id);
        Log::info('Deleting hub ' . $hub->id);
        $this->clearInstitutionCache($hub->campus->institution);
        return parent::destroy($id);
    }

    private function clearCache(Request $request)
    {
        $campus = Campus::find($request->input('campus_id'));
        $this->clearInstitutionCache($campus->institution);
    }

    private function clearInstitutionCache(Institution $institution)
    {
        Log::info("Clearing campi cache for institution {$institution->name}.");
        Cache::forget('campi_institution_' . $institution->id);
    }

    private function validateHub(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'center' => 'required|string',
            'campus_id' => 'required|int',
        ]);
    }
}