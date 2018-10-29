<?php

namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Caronae\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NeighborhoodController extends CrudController
{
    private $zone;

    public function setup()
    {
        $this->crud->setModel('Caronae\Models\Neighborhood');
        $this->crud->setRoute('admin/neighborhoods');
        $this->crud->setEntityNameStrings('bairro', 'bairros');

        if ($zoneId = $this->request->zone) {
            $this->zone = Zone::findOrFail($zoneId);
            $this->crud->addClause('where', 'zone_id', '=', $zoneId);
        }

        $this->crud->setColumns([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [
                'label' => 'Zona',
                'type' => 'select',
                'name' => 'zone_id',
                'entity' => 'zone',
                'attribute' => 'name',
                'model' => 'Caronae\Models\Zone',
            ],
        ]);

        $this->crud->addFields([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [
                'label' => 'Zona',
                'type' => 'select2',
                'name' => 'zone_id',
                'entity' => 'zone',
                'attribute' => 'name',
                'model' => 'Caronae\Models\Zone',
            ],
            [ 'name' => 'distance', 'label' => 'Distância', 'type' => 'number', 'suffix' => 'km', 'default' => '0' ],
        ]);
    }

    public function index()
    {
        $view = parent::index();
        if (!$this->zone) {
            return $view;
        }

        return $view->with([
            'title' => $this->zone->name,
            'subtitle' => 'Bairros desta zona',
        ]);
    }

    public function store(Request $request)
    {
        $this->validateInput($request);

        Log::info('Adding neighborhood ' . $request->name);
        $this->clearCache();

        return parent::storeCrud();
    }

    public function update(Request $request)
    {
        $this->validateInput($request);

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

    private function validateInput(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
        ]);
    }
}
