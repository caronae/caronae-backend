<?php

namespace Caronae\Http\Controllers\Admin;

use Caronae\Http\Controllers\Controller;
use Caronae\ExcelExport\ExcelExporter;
use Caronae\Http\Requests\RankingRequest;
use Caronae\Services\RankingService;

class RankingController extends Controller
{

    public function betterFeedback()
    {
        return view('rankings.better_feedback');
    }

    public function betterFeedbackJson(RankingRequest $request)
    {
        return (new RankingService())->getUsersOrderedByBestFeedbackInPeriod($request->getDate('start'), $request->getDate('end'));
    }

    public function betterFeedbackExcel(RankingRequest $request)
    {
        $data = (new RankingService())->getUsersOrderedByBestFeedbackInPeriod($request->getDate('start'), $request->getDate('end'));

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
        return (new RankingService())->getUsersOrderedByRidesInPeriod($request->getDate('start'), $request->getDate('end'));
    }

    public function greaterRidersExcel(RankingRequest $request)
    {
        $data = (new RankingService())->getUsersOrderedByRidesInPeriod($request->getDate('start'), $request->getDate('end'));

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
        return (new RankingService())->getDriversOrderedByRidesInPeriod($request->getDate('start'), $request->getDate('end'));
    }

    public function greaterDriversRidersExcel(RankingRequest $request)
    {
        $data = (new RankingService())->getDriversOrderedByRidesInPeriod($request->getDate('start'), $request->getDate('end'));

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
        return (new RankingService())->getDriversOrderedByAverageOccupancyInPeriod($request->getDate('start'), $request->getDate('end'));
    }

    public function greaterAverageOccupancyExcel(RankingRequest $request)
    {
        $data = (new RankingService())->getDriversOrderedByAverageOccupancyInPeriod($request->getDate('start'), $request->getDate('end'));

        (new ExcelExporter())->export('motoristas-com-melhor-ocupacao-media',
            ['Nome', 'Perfil UFRJ', 'Curso', 'Caronas', 'Moda', 'Media'],
            $data,
            $request->get('type', 'xlsx')
        );
    }
}
