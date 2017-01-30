<?php 
namespace Caronae\Http\Controllers\Admin2;

use Backpack\CRUD\App\Http\Controllers\CrudController;

use Caronae\Models\User;

// VALIDATION: change the requests to match your own file names if you need form validation
use Caronae\Http\Requests\TagCrudRequest as StoreRequest;
use Caronae\Http\Requests\TagCrudRequest as UpdateRequest;

class UserController extends CrudController {

	public function setup() {
        $this->crud->setModel('Caronae\Models\User');
        $this->crud->setRoute('admin2/users');
        $this->crud->setEntityNameStrings('usuário', 'usuários');
        $this->crud->enableAjaxTable();
        $this->crud->enableDetailsRow();	

        // $this->crud->setFromDb();
        $this->crud->setColumns(['name']);
        $this->crud->addField([
			'name' => 'name',
			'label' => 'Nome'
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
		return $user->name;
	}
}