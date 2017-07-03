<?php
namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\Request;

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
        return parent::storeCrud();
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'zone' => 'required|string',
        ]);
        return parent::updateCrud();
    }
}