<?php 
namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Caronae\Http\Requests\AdminUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends CrudController
{
    public function setup() {
        $this->crud->setModel('Caronae\Models\Admin');
        $this->crud->setRoute('admin/admins');
        $this->crud->setEntityNameStrings('administrador', 'administradores');
        $this->crud->allowAccess(['update', 'delete']);

        $this->crud->setColumns([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'email', 'label' => 'E-mail' ],
            [
                'label' => 'Usuário',
                'type' => 'select',
                'name' => 'user_id',
                'entity' => 'user',
                'attribute' => 'name',
                'model' => 'Caronae\Models\User',
            ],
        ]);

        $this->crud->addFields([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'email', 'label' => 'E-mail', 'type' => 'email' ],
            [ 'name' => 'password', 'label' => 'Senha', 'type' => 'password' ],
            [
                'label' => 'Usuário',
                'type' => 'select2_from_ajax',
                'name' => 'user_id',
                'entity' => 'user',
                'attribute' => 'name',
                'model' => 'Caronae\Models\User',
                'placeholder' => '',
                'minimum_input_length' => 3,
                'data_source' => route('admin-user-search-json')
            ],
        ]);
    }

    public function store(AdminUpdateRequest $request)
    {
        return parent::storeCrud($request);
    }

    public function update(AdminUpdateRequest $request)
    {
        return parent::updateCrud($request);
    }
}