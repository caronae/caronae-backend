<?php 
namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Cache;
use Caronae\Models\Campus;
use Caronae\Models\Institution;
use Illuminate\Http\Request;
use Log;

class InstitutionController extends CrudController
{
    public function setup() {
        $this->crud->setModel('Caronae\Models\Institution');
        $this->crud->setRoute('admin/institutions');
        $this->crud->setEntityNameStrings('instituição', 'instituições');
        $this->crud->allowAccess(['show']);
        $this->crud->setShowView('institutions.show');

        $this->crud->setColumns([
            [ 'name' => 'id', 'label' => 'ID' ],
            [ 'name' => 'name', 'label' => 'Nome' ],
        ]);

        $this->crud->addFields([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'slug', 'label' => 'Slug' ],
            [ 'name' => 'authentication_url', 'label' => 'URL de autenticação', 'type' => 'url' ],
            [ 'name' => 'login_message', 'label' => 'Mensagem de login', 'type' => 'wysiwyg' ],
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

    public function store()
    {
        return parent::storeCrud();
    }

    public function update(Request $request)
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

        $this->clearCache($institution);
        return parent::updateCrud($request);
    }

    private function clearCache(Institution $institution)
    {
        Log::info("Clearing campi cache for institution {$institution->name}.");
        Cache::forget('campi_institution_' . $institution->id);
    }
}