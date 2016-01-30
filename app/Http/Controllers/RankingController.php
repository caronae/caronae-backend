<?php

namespace App\Http\Controllers;

use App\ExcelExporter;
use App\Http\Requests\RankingRequest;
use App\RankingService;
use Carbon\Carbon;
use DB;
use Symfony\Component\HttpFoundation\Request;

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

    public function baseQuery(Carbon $periodStart, Carbon $periodEnd){
        // this method returns a common base query that all other methods use.
        return DB::table('users')
            ->leftJoin('ride_user', function($join){
                $join->on('users.id', '=', 'ride_user.user_id');

            })->leftJoin('rides', function($join)  {
                $join->on('ride_user.ride_id', '=', 'rides.id');
            })
            ->whereNull('users.deleted_at')
            ->where('rides.done', '=', true)
            ->where('rides.mydate', '>=', $periodStart->format("Y-m-d"))
            ->where('rides.mydate', '<=', $periodEnd->format("Y-m-d"));
    }

    public function getTaxaDeCarbono($periodStart, $periodEnd)
    {
        return $this->baseQuery($periodStart, $periodEnd)
            ->leftJoin('neighborhoods', function($join){
                $join->on('rides.myzone', '=', 'neighborhoods.zone');
                $join->on('rides.neighborhood', '=', 'neighborhoods.name');
            })

            ->where('rides.mydate', '>=', DB::raw("
                (SELECT mydate
                 FROM users as u
                 JOIN ride_user ON users.id = ride_user.user_id
                 JOIN rides ON rides.id = ride_user.ride_id
                 WHERE u.id = users.id AND
                       ride_user.status = 'driver' AND
                       rides.done = true
                 ORDER BY mydate ASC
                 LIMIT 1
                 )"))

            ->where('ride_user.status', '=', 'accepted')

            ->select(
                // 131 é um valor mágico. É a taxa media de carbono emitido por um carro no Brasil
                DB::raw('SUM(neighborhoods.distance * 131) as carbono_economizado')
            )->get()[0]->carbono_economizado;
    }

    public function taxaDeCarbono(Request $request){

        $start = Carbon::createFromFormat('d/m/Y', $request->get('start', Carbon::now()->format('d/m/Y')));

        $end = Carbon::createFromFormat('d/m/Y', $request->get('end', Carbon::now()->subMonth()->format('d/m/Y')));

        if($start->gt($end)) view('taxaDeCarbono.index')->with('taxa', null)->with('errou', 'O fim do período deve ser depois do começo do período.');

        $taxa = $this->getTaxaDeCarbono($start, $end);

        return view('taxaDeCarbono.index')->with('taxa', $taxa);
    }
}
