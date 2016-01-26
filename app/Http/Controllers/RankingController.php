<?php

namespace App\Http\Controllers;

use App\ExcelExporter;
use App\Http\Requests\RankingRequest;
use App\RankingService;
use App\Ride;
use App\RideUser;
use App\User;
use Carbon\Carbon;
use DB;
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

    public function greaterCaronistas()
    {
        return view('rankings.greater_caronistas');
    }

    public function greaterCaronistasJson(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getUsersOrderedByCaronasInPeriod($start, $end);
        return $data;
    }

    public function greaterCaronistasExcel(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getUsersOrderedByCaronasInPeriod($start, $end);

        (new ExcelExporter())->export('caronistas-com-mais-caronas',
            ['Nome', 'Perfil UFRJ', 'Curso', 'Caronas'],
            $data,
            $request->get('type', 'xlsx')
        );
    }

    public function greaterDriversCaronistas()
    {
        return view('rankings.greater_drivers_caronistas');
    }

    public function greaterDriversCaronistasJson(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getDriversOrderedByCaronasInPeriod($start, $end);
        return $data;
    }

    public function greaterDriversCaronistasExcel(RankingRequest $request)
    {
        $start = Carbon::createFromFormat('d/m/Y', $request->get('start'));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end'));

        $data = with(new RankingService())->getDriversOrderedByCaronasInPeriod($start, $end);

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
}
