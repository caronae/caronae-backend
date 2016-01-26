<?php

namespace App\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AllViewComposer
{
    public function compose(View $view){
        // allows the local override of $message during includes
        // example: @include('view', ['message' => 'oi'])
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