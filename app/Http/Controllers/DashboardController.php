<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Feeder;
use App\Models\SubDivision;
use App\Models\Substation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user        = $request->user();
        $summary     = $this->buildSummary($user);
        $divisions   = $this->getDivisions($user);
        $subDivisions = $this->getSubDivisions($user);

        return view('dashboard.index', compact('summary', 'divisions', 'subDivisions'));
    }

    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->buildSummary($request->user()));
    }

    private function buildSummary($user): array
    {
        $query = Feeder::query();
        $this->applyJurisdictionScope($query, $user);

        /** @var object{total:int, fullyOn:int, partialOn:int, fullyOff:int} $row */
        $row = (clone $query)->selectRaw("
            COUNT(*) as total,
            SUM(current_status = 'fully_on')    as fullyOn,
            SUM(current_status = 'partially_on') as partialOn,
            SUM(current_status = 'fully_off')   as fullyOff
        ")->first();

        return [
            'total'     => (int) $row->total,
            'fullyOn'   => (int) $row->fullyOn,
            'partialOn' => (int) $row->partialOn,
            'fullyOff'  => (int) $row->fullyOff,
        ];
    }

    private function getDivisions($user): \Illuminate\Support\Collection
    {
        $circleFilter = $user->isCircleScoped() ? $user->jurisdiction_id : null;

        $rows = DB::select("
            SELECT
                d.id,
                d.name,
                COUNT(f.id)                                        AS total_feeders,
                SUM(f.current_status = 'fully_on')                AS feeders_on,
                SUM(f.current_status = 'partially_on')            AS feeders_partial,
                SUM(f.current_status = 'fully_off')               AS feeders_off
            FROM divisions d
            JOIN sub_divisions sd ON sd.division_id = d.id
            JOIN substations ss   ON ss.sub_division_id = sd.id
            JOIN feeders f        ON f.substation_id = ss.id
            " . ($circleFilter ? "WHERE d.circle_id = ?" : "") . "
            GROUP BY d.id, d.name
            ORDER BY d.name
        ", $circleFilter ? [$circleFilter] : []);

        return collect($rows);
    }

    private function getSubDivisions($user): \Illuminate\Support\Collection
    {
        $circleFilter = $user->isCircleScoped() ? $user->jurisdiction_id : null;

        $rows = DB::select("
            SELECT
                sd.id,
                sd.name,
                d.name                                             AS division_name,
                COUNT(f.id)                                        AS total_feeders,
                SUM(f.current_status = 'fully_on')                AS feeders_on,
                SUM(f.current_status = 'partially_on')            AS feeders_partial,
                SUM(f.current_status = 'fully_off')               AS feeders_off
            FROM sub_divisions sd
            JOIN divisions d      ON d.id = sd.division_id
            JOIN substations ss   ON ss.sub_division_id = sd.id
            JOIN feeders f        ON f.substation_id = ss.id
            " . ($circleFilter ? "WHERE d.circle_id = ?" : "") . "
            GROUP BY sd.id, sd.name, d.name
            ORDER BY sd.name, d.name
        ", $circleFilter ? [$circleFilter] : []);

        return collect($rows);
    }

    private function applyJurisdictionScope(\Illuminate\Database\Eloquent\Builder $query, $user): void
    {
        if ($user->hasRole('admin')) {
            return;
        }

        if ($user->isCircleScoped()) {
            $substationIds = Substation::whereIn(
                'sub_division_id',
                SubDivision::whereIn(
                    'division_id',
                    Division::where('circle_id', $user->jurisdiction_id)->pluck('id')
                )->pluck('id')
            )->pluck('id');

            $query->whereIn('substation_id', $substationIds);
        }
    }
}
