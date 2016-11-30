<?php

namespace Caronae\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ErrorViewComposer
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function compose(View $view){
        $view->with([
            'url' => $this->request->url(),
            'returnURL' => route('home'),
        ]);
    }
}