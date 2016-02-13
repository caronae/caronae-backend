<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarbonTaxRequest;
use App\Services\CarbonTaxService;
use Carbon\Carbon;

class CarbonTaxController extends Controller
{
    public function carbonTax(CarbonTaxRequest $request){

        $start = Carbon::createFromFormat('d/m/Y', $request->get('start', Carbon::now()->subMonth()->format('d/m/Y')));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end', Carbon::now()->format('d/m/Y')));

        $view = view('carbon-tax.index');

        $taxa = (new CarbonTaxService())->getCarbonTaxSaved($start, $end);

        return $view->with(['taxa' => $taxa, 'start' => $start, 'end' => $end]);
    }
}
