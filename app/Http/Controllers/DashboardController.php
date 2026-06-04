<?php

namespace App\Http\Controllers;

use App\Models\Feeder;
use App\Models\Division;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user    = $request->user();
        $summary = $this->buildSummary($user);
        $divisions = $this->getDivisions($user);

        return view('dashboard.index', compact('summary', 'divisions'));
    }

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json($this->buildSummary($user));
    }

    private function buildSummary($user): array
    {
        $query = Feeder::query();
        $this->applyJurisdictionScope($query, $user);

        $total      = (clone $query)->count();
        $fullyOn    = (clone $query)->where('current_status', 'fully_on')->count();
        $partialOn  = (clone $query)->where('current_status', 'partially_on')->count();
        $fullyOff   = (clone $query)->where('current_status', 'fully_off')->count();

        return compact('total', 'fullyOn', 'partialOn', 'fullyOff');
    }

    private function getDivisions($user): \Illuminate\Database\Eloquent\Collection
    {
        $query = Division::withCount([
            'subDivisions as total_feeders' => fn($q) => $q->join('substations', 'substations.sub_division_id', 'sub_divisions.id')
                ->join('feeders', 'feeders.substation_id', 'substations.id')
                ->select(\DB::raw('count(feeders.id)')),
            'subDivisions as feeders_on' => fn($q) => $q->join('substations', 'substations.sub_division_id', 'sub_divisions.id')
                ->join('feeders', 'feeders.substation_id', 'substations.id')
                ->where('feeders.current_status', 'fully_on')
                ->select(\DB::raw('count(feeders.id)')),
            'subDivisions as feeders_partial' => fn($q) => $q->join('substations', 'substations.sub_division_id', 'sub_divisions.id')
                ->join('feeders', 'feeders.substation_id', 'substations.id')
                ->where('feeders.current_status', 'partially_on')
                ->select(\DB::raw('count(feeders.id)')),
            'subDivisions as feeders_off' => fn($q) => $q->join('substations', 'substations.sub_division_id', 'sub_divisions.id')
                ->join('feeders', 'feeders.substation_id', 'substations.id')
                ->where('feeders.current_status', 'fully_off')
                ->select(\DB::raw('count(feeders.id)')),
        ]);

        if ($user->hasRole('circle')) {
            $query->where('circle_id', $user->jurisdiction_id);
        }

        return $query->get();
    }

    private function applyJurisdictionScope(\Illuminate\Database\Eloquent\Builder $query, $user): void
    {
        if ($user->hasRole('admin')) {
            return;
        }

        if ($user->hasRole('circle')) {
            $query->whereHas('substation.subDivision.division', fn($q) =>
                $q->where('circle_id', $user->jurisdiction_id)
            );
        }
    }
}
