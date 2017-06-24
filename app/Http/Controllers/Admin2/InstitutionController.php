<?php 
namespace Caronae\Http\Controllers\Admin2;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Caronae\Models\Institution;
use Illuminate\Http\Request;

class InstitutionController extends CrudController
{
    public function setup() {
        $this->crud->setModel('Caronae\Models\Institution');
        $this->crud->setRoute('admin2/institutions');
        $this->crud->setEntityNameStrings('instituição', 'instituições');
        $this->crud->denyAccess(['edit', 'delete']);
        $this->crud->enableDetailsRow();

        $this->crud->setColumns([
            [ 'name' => 'name', 'label' => 'Nome' ],
        ]);

        $this->crud->addFields([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'authentication_url', 'label' => 'URL de autenticação', 'type' => 'url' ],
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
        ]);
        return parent::updateCrud();
    }

    public function showDetailsRow($id)
    {
        $institution = Institution::find($id);
        return view('vendor.backpack.crud.inc.institution', ['institution' => $institution]);
    }
}