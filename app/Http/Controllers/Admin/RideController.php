<?php

namespace Caronae\Http\Controllers\Admin;

use Caronae\Http\Controllers\Controller;
use Caronae\ExcelExport\ExcelExporter;
use Caronae\Http\Requests\RankingRequest;
use Caronae\Models\Ride;

class RideController extends Controller
{
    public function index()
    {
        return view('rides.index');
    }

    public function indexJson(RankingRequest $request)
    {
        // o RankingRequest está sendo usado para reutilizar código, mas isso tecnicamente não é um ranking
        return Ride::getInPeriodWithUserInfo($request->getDate('start'), $request->getDate('end'));
    }

    public function indexExcel(RankingRequest $request)
    {
        $data = Ride::getInPeriodWithUserInfo($request->getDate('start'), $request->getDate('end'));

        (new ExcelExporter())->exportWithBlade('caronas-dadas', 'rides.excel-export-sheet',
        ['Motorista', 'Curso', 'Data', 'Hora', 'Origem', 'Destino', 'Distancia', 'Distancia Total', 'Total de Caronas', 'Distancia Média'],
        $data->toArray(), $request->get('type', 'xlsx'));
    }

    public function riders($rideId)
    {
        return Ride::find($rideId)->riders();
    }

}