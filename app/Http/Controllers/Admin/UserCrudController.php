<?php 
namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\App\Http\Controllers\CrudController;

use Caronae\Models\User;

// VALIDATION: change the requests to match your own file names if you need form validation
use Caronae\Http\Requests\TagCrudRequest as StoreRequest;
use Caronae\Http\Requests\TagCrudRequest as UpdateRequest;

class UserCrudController extends CrudController {

	public function setup() {
        $this->crud->setModel("Caronae\Models\User");
        $this->crud->setRoute("admin/user");
        $this->crud->setEntityNameStrings('user', 'users');
        $this->crud->enableAjaxTable();
        $this->crud->enableDetailsRow();	

        // $this->crud->setFromDb();
        $this->crud->setColumns(['name']);
        $this->crud->addField([
		'name' => 'name',
		'label' => "Tag name"
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