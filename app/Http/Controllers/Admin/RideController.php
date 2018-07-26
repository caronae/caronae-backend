<?php

namespace Caronae\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

class RideController extends CrudController
{
    public function setup()
    {
        $this->crud->setModel('Caronae\Models\Ride');
        $this->crud->setRoute('admin/rides');
        $this->crud->setEntityNameStrings('carona', 'caronas');
        $this->crud->orderBy('date', 'DESC');
        $this->crud->enableExportButtons();
        $this->crud->setDefaultPageLength(10);
        $this->crud->allowAccess(['show']);
        $this->crud->removeButton('update');
        $this->crud->denyAccess(['create', 'update', 'delete']);
        $this->crud->setShowView('rides.show');

        $this->crud->setColumns([
            [
                'label' => 'Motorista',
                'type' => 'model_function_attribute',
                'name' => 'driver',
                'function_name' => 'driver',
                'attribute' => 'name',
            ],
            ['name' => 'date', 'label' => 'Data', 'type' => 'datetime'],
            ['name' => 'origin', 'label' => 'Origem'],
            ['name' => 'destination', 'label' => 'Destino'],
        ]);

        $this->crud->addFilter(
            [
                'type' => 'date_range',
                'name' => 'from_to',
                'label' => 'Período'
            ],
            false,
            function ($value) {
                $dates = json_decode($value);
                $this->crud->addClause('where', 'date', '>=', $dates->from);
                $this->crud->addClause('where', 'date', '<=', $dates->to);
            }
        );

        $this->crud->addFilter(
            [
                'type' => 'simple',
                'name' => 'finished',
                'label' => 'Concluídas'
            ],
            false,
            function () {
                $this->crud->addClause('finished');
            }
        );

        $this->crud->addFilter(
            [
                'type' => 'simple',
                'name' => 'routine',
                'label' => 'Rotinas'
            ],
            false,
            function () {
                $this->crud->addClause('whereHas', 'routine');
            }
        );

    }


}