<?php 
namespace Caronae\Http\Controllers\Admin2;

use Backpack\CRUD\App\Http\Controllers\CrudController;
use Caronae\Models\User;
use Illuminate\Http\Request;

class UserController extends CrudController
{
    public function setup() {
        $this->crud->setModel('Caronae\Models\User');
        $this->crud->setRoute('admin2/users');
        $this->crud->setEntityNameStrings('usuário', 'usuários');
        $this->crud->enableAjaxTable();
        $this->crud->enableDetailsRow();
        $this->crud->removeButton('delete');
        $this->crud->addButtonFromView('line', 'Banir', 'ban', 1);
        $this->crud->enableExportButtons();
        $this->crud->setDefaultPageLength(10);

        $this->crud->addFilter(
            [
              'type' => 'simple',
              'name' => 'banned',
              'label' => 'Banidos'
            ],
            false,
            function($values) {
                $this->crud->query = $this->crud->query->where('banned', true);
            }
        );

        $this->crud->setColumns([
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'profile', 'label' => 'Perfil' ],
            [ 'name' => 'course', 'label' => 'Curso' ],
            [ 'name' => 'location', 'label' => 'Bairro' ],
        ]);
        
        $this->crud->addFields([
            [ 'name' => 'profile_pic_url', 'label' => 'Foto', 'type' => 'image' ],
            [ 'name' => 'name', 'label' => 'Nome' ],
            [ 'name' => 'email', 'label' => 'E-mail', 'type' => 'email' ],
            [ 'name' => 'phone_number', 'label' => 'Telefone' ],
            [ 'name' => 'profile', 'label' => 'Perfil' ],
            [ 'name' => 'course', 'label' => 'Curso' ],
            [ 'name' => 'location', 'label' => 'Bairro' ],
            [ 'name' => 'id_ufrj', 'label' => 'ID UFRJ' ],
            [ 'name' => 'token', 'label' => 'Chave', 'type' => 'password' ],
            [ 'name' => 'car_owner', 'label' => 'Possui carro', 'type' => 'checkbox' ],
            [ 'name' => 'car_model', 'label' => 'Modelo do carro' ],
            [ 'name' => 'car_plate', 'label' => 'Placa do carro' ],
            [ 'name' => 'car_color', 'label' => 'Cor do carro' ],
        ]);
    }

    public function store(Request $request)
    {
        return parent::storeCrud();
    }

    public function update(Request $request)
    {
        return parent::updateCrud();
    }

    public function showDetailsRow($id)
    {
        $user = User::find($id);
        return view('vendor.backpack.crud.inc.user', ['u' => $user]);
    }

    public function ban(Request $request, User $user)
    {
        $user->banish();
        return response()->json(['message' => 'User banned']);
    }

    public function unban(Request $request, User $user)
    {
        $user->unban();
        return response()->json(['message' => 'User unbanned']);
    }
}