<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarbonTaxRequest;
use App\Services\CarbonTaxService;
use Carbon\Carbon;

class CarbonTaxController extends Controller
{
    public function carbonTax(CarbonTaxRequest $request){

        $start = $request->getDate('start', Carbon::now()->subMonth());

        $end = $request->getDate('end', Carbon::now());

        $view = view('carbon-tax.index');

        $taxa = (new CarbonTaxService())->getCarbonTaxSaved($start, $end);

        return $view->with(['taxa' => $taxa, 'start' => $start, 'end' => $end]);
    }
}
