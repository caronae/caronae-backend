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

        $this->crud->setColumns([
            [ 'name' => 'name', 'label' => 'Nome' ],
        ]);

        $this->crud->addFields([
            [ 'name' => 'name', 'label' => 'Nome' ],
        ]);
    }

    public function store(Request $request)
    {
        return parent::storeCrud();
    }

}