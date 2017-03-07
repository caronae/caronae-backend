<?php

namespace Caronae\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AllViewComposer
{
    public function compose(View $view){
        // Permite que o valor de message seja sobrescrito
        // em uma view no momento do include.
        // Exemplo: @include('view', ['message' => 'oi'])
        if(!isset($view->getData()['message'])) {
            $view->with([
                'message' => session()->get('message', '')
            ]);
        }
        
        if(auth()->user()){
            $view->with(['user' => auth()->user()]);
        }
    }
}