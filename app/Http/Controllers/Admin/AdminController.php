<?php 
namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

class AdminController extends CrudController
{
    public function setup() {
        $this->crud->setModel('Caronae\Models\Admin');
        $this->crud->setRoute('admin/admins');
        $this->crud->setEntityNameStrings('administrador', 'administradores');
        $this->crud->denyAccess(['edit', 'delete']);

        $this->crud->setColumns([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'email', 'label' => 'E-mail' ],
        ]);

        $this->crud->addFields([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'email', 'label' => 'E-mail', 'type' => 'email' ],
            [ 'name' => 'password', 'label' => 'Senha', 'type' => 'password' ],
        ]);
    }

    public function store()
    {
        return parent::storeCrud();
    }

    public function update()
    {
        return parent::updateCrud();
    }
}