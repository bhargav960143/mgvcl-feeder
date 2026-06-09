<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFeederStatusRequest;
use App\Models\Division;
use App\Models\Feeder;
use App\Models\FeederCategory;
use App\Models\SubDivision;
use App\Models\Substation;
use App\Services\FeederStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeederController extends Controller
{
    public function __construct(private FeederStatusService $statusService) {}

    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = Feeder::with(['substation.subDivision.division', 'lastUpdatedBy'])
            ->join('substations as ss',      'feeders.substation_id',   '=', 'ss.id')
            ->join('sub_divisions as sd',    'ss.sub_division_id',      '=', 'sd.id')
            ->join('divisions as dv',        'sd.division_id',          '=', 'dv.id')
            ->select('feeders.*')
            ->orderBy('dv.name')
            ->orderBy('sd.name')
            ->orderBy('feeders.name');

        // Jurisdiction scoping via whereIn (avoids correlated EXISTS subqueries)
        if ($user->isCircleScoped()) {
            $query->whereIn('substation_id', $this->substationIdsForCircle($user->jurisdiction_id));
        } elseif ($user->hasRole('division_manager')) {
            $query->whereIn('substation_id', $this->substationIdsForDivision($user->jurisdiction_id));
        } elseif ($user->hasRole('sub_division_manager')) {
            $query->whereIn('substation_id',
                Substation::where('sub_division_id', $user->jurisdiction_id)->pluck('id')
            );
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('tnd_code', 'like', "%{$search}%")
            );
        }
        if ($request->filled('division_id') && $user->hasAnyRole(['admin', 'circle', 'circle_viewer'])) {
            $query->whereIn('substation_id', $this->substationIdsForDivision($request->division_id));
        }
        if ($request->filled('sub_division_id') && $user->hasAnyRole(['admin', 'circle', 'circle_viewer', 'division_manager'])) {
            $query->whereIn('substation_id',
                Substation::where('sub_division_id', $request->sub_division_id)->pluck('id')
            );
        }

        $feeders = $query->get();

        $divisions    = $this->getDivisionsForFilter($user);
        $subDivisions = $this->getSubDivisionsForFilter($user, $request->division_id);

        $statusLabels = [
            'fully_on'     => 'Fully ON',
            'partially_on' => 'Partially ON',
            'fully_off'    => 'Fully OFF',
        ];

        $categories = Cache::remember('feeder_categories', 3600, fn() => FeederCategory::orderBy('name')->pluck('name'));

        return view('feeders.index', compact(
            'feeders', 'divisions', 'subDivisions', 'statusLabels', 'categories'
        ));
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'feeder_ids'   => ['required', 'array', 'min:1', 'max:500'],
            'feeder_ids.*' => ['integer', 'exists:feeders,id'],
            'status'       => ['required', Rule::in(['fully_on', 'partially_on', 'fully_off'])],
            'remarks'      => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $feeders = Feeder::with(['substation.subDivision.division'])
            ->whereIn('id', $request->feeder_ids)
            ->get()
            ->filter(fn($feeder) => $user->can('updateStatus', $feeder));

        if ($feeders->isEmpty()) {
            return back()->with('error', 'No feeders authorized for bulk update.');
        }

        foreach ($feeders as $feeder) {
            $this->statusService->updateStatus($feeder, $request->status, $request->remarks, $user);
        }

        return back()->with('success', "{$feeders->count()} feeder(s) updated to {$request->status}.");
    }

    public function updateStatus(UpdateFeederStatusRequest $request, Feeder $feeder): RedirectResponse|JsonResponse
    {
        $this->statusService->updateStatus(
            $feeder,
            $request->status,
            $request->remarks,
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "Feeder [{$feeder->name}] status updated to {$request->status}.");
    }

    private function substationIdsForCircle(int $circleId): \Illuminate\Support\Collection
    {
        return Substation::whereIn(
            'sub_division_id',
            SubDivision::whereIn(
                'division_id',
                Division::where('circle_id', $circleId)->pluck('id')
            )->pluck('id')
        )->pluck('id');
    }

    private function substationIdsForDivision(int $divisionId): \Illuminate\Support\Collection
    {
        return Substation::whereIn(
            'sub_division_id',
            SubDivision::where('division_id', $divisionId)->pluck('id')
        )->pluck('id');
    }

    private function getDivisionsForFilter($user): \Illuminate\Support\Collection
    {
        if ($user->hasAnyRole(['admin', 'circle', 'circle_viewer'])) {
            $query = Division::orderBy('name');
            if ($user->isCircleScoped()) {
                $query->where('circle_id', $user->jurisdiction_id);
            }
            return $query->get();
        }

        return collect();
    }

    private function getSubDivisionsForFilter($user, ?string $divisionId): \Illuminate\Support\Collection
    {
        if ($user->hasAnyRole(['admin', 'circle', 'circle_viewer'])) {
            $query = SubDivision::orderBy('name');
            if ($divisionId) {
                $query->where('division_id', $divisionId);
            } elseif ($user->isCircleScoped()) {
                $query->whereIn(
                    'division_id',
                    Division::where('circle_id', $user->jurisdiction_id)->pluck('id')
                );
            }
            return $query->get();
        }

        if ($user->hasRole('division_manager')) {
            return SubDivision::where('division_id', $user->jurisdiction_id)->orderBy('name')->get();
        }

        return collect();
    }
}
