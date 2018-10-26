<?php

namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Caronae\Models\User;
use Illuminate\Http\Request;

class UserController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel('Caronae\Models\User');
        $this->crud->setRoute('admin/users');
        $this->crud->setEntityNameStrings('usuário', 'usuários');
        $this->crud->removeButton('delete');
        $this->crud->addButtonFromView('line', 'Banir', 'ban', 1);
        $this->crud->enableExportButtons();
        $this->crud->setDefaultPageLength(10);
        $this->crud->allowAccess(['show']);
        $this->crud->setShowView('users.show');

        $this->crud->addFilter(
            [
                'type' => 'simple',
                'name' => 'banned',
                'label' => 'Banidos'
            ],
            false,
            function () {
                $this->crud->query = $this->crud->query->where('banned', true);
            }
        );

        $this->crud->setColumns([
            [
                'name' => 'name',
                'label' => 'Nome',
                'searchLogic' => function ($query, $column, $searchTerm) {
                    $names = array_filter(explode(' ', $searchTerm));
                    foreach ($names as $searchName) {
                        $searchName = trim($searchName);
                        $query->where('name', 'ILIKE', "%{$searchName}%");
                    }
                }
            ],
            ['name' => 'profile', 'label' => 'Perfil'],
            ['name' => 'course', 'label' => 'Curso'],
            ['name' => 'location', 'label' => 'Bairro'],
            [
                'label' => 'Instituição',
                'type' => 'select',
                'name' => 'institution_id',
                'entity' => 'institution',
                'attribute' => 'name',
                'model' => 'Caronae\Models\Institution',
            ],
        ]);

        $this->crud->addFields([
            ['name' => 'profile_pic_url', 'label' => 'Foto', 'type' => 'image'],
            ['name' => 'name', 'label' => 'Nome'],
            ['name' => 'email', 'label' => 'E-mail', 'type' => 'email'],
            [
                'label' => 'Instituição',
                'type' => 'select2',
                'name' => 'institution_id',
                'entity' => 'institution',
                'attribute' => 'name',
                'model' => 'Caronae\Models\Institution',
            ],
            ['name' => 'phone_number', 'label' => 'Telefone'],
            ['name' => 'profile', 'label' => 'Perfil'],
            ['name' => 'course', 'label' => 'Curso'],
            ['name' => 'location', 'label' => 'Bairro'],
            ['name' => 'id_ufrj', 'label' => 'ID UFRJ'],
            ['name' => 'token', 'label' => 'Chave', 'type' => 'password'],
            ['name' => 'car_owner', 'label' => 'Possui carro', 'type' => 'checkbox'],
            ['name' => 'car_model', 'label' => 'Modelo do carro'],
            ['name' => 'car_plate', 'label' => 'Placa do carro'],
            ['name' => 'car_color', 'label' => 'Cor do carro'],
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

    public function ban(User $user)
    {
        $user->banish();
        return response()->json(['message' => 'User banned']);
    }

    public function unban(User $user)
    {
        $user->unban();
        return response()->json(['message' => 'User unbanned']);
    }

    public function searchJson(Request $request)
    {
        $search_term = $request->input('q');
        $page = $request->input('page');

        if ($search_term)
        {
            $results = User::where('name', 'LIKE', '%'.$search_term.'%')->paginate(10);
        } else {
            $results = User::paginate(10);
        }

        return $results;
    }

    public function showJson($id)
    {
        return User::find($id);
    }
}
