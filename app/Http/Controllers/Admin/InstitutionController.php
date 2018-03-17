<?php 
namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Requests\CrudRequest;
use Cache;
use Caronae\Models\Campus;
use Caronae\Models\Institution;
use Log;

class InstitutionController extends CrudController
{
    public function setup() {
        $this->crud->setModel('Caronae\Models\Institution');
        $this->crud->setRoute('admin/institutions');
        $this->crud->setEntityNameStrings('instituição', 'instituições');
        $this->crud->denyAccess(['edit', 'delete']);
        $this->crud->allowAccess(['show']);

        $this->crud->setColumns([
            [ 'name' => 'id', 'label' => 'ID' ],
            [ 'name' => 'name', 'label' => 'Nome' ],
        ]);

        $this->crud->addFields([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'authentication_url', 'label' => 'URL de autenticação', 'type' => 'url' ],
            [
                'name' => 'campi',
                'label' => 'Campi',
                'type' => 'table',
                'entity_singular' => 'campus',
                'columns' => [
                    'name' => 'Nome',
                    'color' => 'Cor',
                ],
                'min' => 1
            ],
            [ 'name' => 'going_label', 'label' => 'Label de chegada' ],
            [ 'name' => 'leaving_label', 'label' => 'Label de saída' ],
        ]);
    }

    public function show($id)
    {
        $institution = Institution::findOrFail($id);
        $campi = $institution->campi()->get();
        $this->data['title'] = $institution->name;

        return view('institutions.show', $this->data, ['institution' => $institution, 'campi' => $campi]);
    }

    public function store()
    {
        return parent::storeCrud();
    }

    public function update(CrudRequest $request)
    {
         $this->validate($request, [
             'name' => 'required|string',
             'authentication_url' => 'required|url',
             'going_label' => 'required|string',
             'leaving_label' => 'required|string',
         ]);

        $institution = Institution::find($request->institution);

        $campi = collect(json_decode($request->input('campi'), true));
        $campi->each(function($campusRequest) use ($institution) {
            if (empty($campusRequest)) return;

            if (isset($campusRequest['id'])) {
                $campus = Campus::findOrFail($campusRequest['id']);
            } else {
                $campus = new Campus();
            }

            $campus->fill($campusRequest);
            $institution->campi()->save($campus);
        });

        $this->clearCache();
        return parent::updateCrud($request);
    }

    private function clearCache()
    {
        Log::info('Clearing campi cache.');
        Cache::forget('campi');
    }
}