<?php

namespace App\Http\Controllers;

use App\ExcelExport\ExcelExporter;
use App\Http\Requests\RankingRequest;
use App\RankingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RankingController extends Controller
{

    public function betterFeedback()
    {
        return view('rankings.better_feedback');
    }

    public function betterFeedbackJson(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getUsersOrderedByBestFeedbackInPeriod($start, $end);
        return $data;
    }

    public function betterFeedbackExcel(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getUsersOrderedByBestFeedbackInPeriod($start, $end);

        (new ExcelExporter())->export('motoristas-melhor-avaliados',
            ['Nome', 'Perfil UFRJ', 'Curso', 'Caronas Dadas', 'Caronistas Levados', 'Feedback Positivo', 'Feedback Negativo', 'Sem Feedback', 'Reputação'],
            $data,
            $request->get('type', 'xlsx')
        );
    }

    public function greaterRiders()
    {
        return view('rankings.greater_riders');
    }

    public function greaterRidersJson(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getUsersOrderedByRidesInPeriod($start, $end);
        return $data;
    }

    public function greaterRidersExcel(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getUsersOrderedByRidesInPeriod($start, $end);

        (new ExcelExporter())->export('caronistas-com-mais-caronas',
            ['Nome', 'Perfil UFRJ', 'Curso', 'Caronas'],
            $data,
            $request->get('type', 'xlsx')
        );
    }

    public function greaterDriversRiders()
    {
        return view('rankings.greater_drivers_riders');
    }

    public function greaterDriversRidersJson(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getDriversOrderedByRidesInPeriod($start, $end);
        return $data;
    }

    public function greaterDriversRidersExcel(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getDriversOrderedByRidesInPeriod($start, $end);

        (new ExcelExporter())->export('motoristas-com-mais-caronas',
            ['Nome', 'Perfil UFRJ', 'Curso', 'Carbono Economizado', 'Caronas'],
            $data,
            $request->get('type', 'xlsx')
        );
    }

    public function greaterAverageOccupancy()
    {
        return view('rankings.greater_average_occupancy');
    }

    public function greaterAverageOccupancyJson(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getDriversOrderedByAverageOccupancyInPeriod($start, $end);
        return $data;
    }

    public function greaterAverageOccupancyExcel(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getDriversOrderedByAverageOccupancyInPeriod($start, $end);

        (new ExcelExporter())->export('motoristas-com-melhor-ocupacao-media',
            ['Nome', 'Perfil UFRJ', 'Curso', 'Caronas', 'Moda', 'Media'],
            $data,
            $request->get('type', 'xlsx')
        );
    }

    public function carbonTax(Request $request){

        $start = Carbon::createFromFormat('d/m/Y', $request->get('start', Carbon::now()->subMonth()->format('d/m/Y')));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end', Carbon::now()->format('d/m/Y')));

        $view = view('carbonTax.index');

        if($start->gt($end))
            return $view->with('taxa', null)->with('errou', 'O fim do período deve ser depois do começo do período.');

        $taxa = (new RankingService)->getCarbonTaxSaved($start, $end);

        return $view->with('taxa', $taxa);
    }
}
