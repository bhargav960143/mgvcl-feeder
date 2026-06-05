<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFeederStatusRequest;
use App\Models\Division;
use App\Models\Feeder;
use App\Models\SubDivision;
use App\Services\FeederStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeederController extends Controller
{
    public function __construct(private FeederStatusService $statusService) {}

    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = Feeder::with(['substation.subDivision.division', 'lastUpdatedBy'])
            ->orderBy('name');

        // Jurisdiction scoping
        if ($user->hasRole('circle')) {
            $query->whereHas('substation.subDivision.division', fn($q) =>
                $q->where('circle_id', $user->jurisdiction_id)
            );
        } elseif ($user->hasRole('division_manager')) {
            $query->whereHas('substation.subDivision', fn($q) =>
                $q->where('division_id', $user->jurisdiction_id)
            );
        } elseif ($user->hasRole('sub_division_manager')) {
            $query->whereHas('substation', fn($q) =>
                $q->where('sub_division_id', $user->jurisdiction_id)
            );
        }

        // Filters
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
        if ($request->filled('division_id') && $user->hasAnyRole(['admin', 'circle'])) {
            $query->whereHas('substation.subDivision', fn($q) =>
                $q->where('division_id', $request->division_id)
            );
        }
        if ($request->filled('sub_division_id') && $user->hasAnyRole(['admin', 'circle', 'division_manager'])) {
            $query->whereHas('substation', fn($q) =>
                $q->where('sub_division_id', $request->sub_division_id)
            );
        }

        $feeders = $query->get();

        // Build filter dropdowns (scoped to jurisdiction)
        $divisions    = $this->getDivisionsForFilter($user);
        $subDivisions = $this->getSubDivisionsForFilter($user, $request->division_id);

        $statusLabels = [
            'fully_on'     => 'Fully ON',
            'partially_on' => 'Partially ON',
            'fully_off'    => 'Fully OFF',
        ];

        $categories = ['URBAN', 'GIDC', 'HTEX', 'EHT', 'SST', 'IND'];

        return view('feeders.index', compact(
            'feeders', 'divisions', 'subDivisions', 'statusLabels', 'categories'
        ));
    }

    public function updateStatus(UpdateFeederStatusRequest $request, Feeder $feeder): RedirectResponse
    {
        $this->statusService->updateStatus(
            $feeder,
            $request->status,
            $request->remarks,
            $request->user()
        );

        return back()->with('success', "Feeder [{$feeder->name}] status updated to {$request->status}.");
    }

    private function getDivisionsForFilter($user): \Illuminate\Database\Eloquent\Collection
    {
        if ($user->hasAnyRole(['admin', 'circle'])) {
            $query = Division::orderBy('name');
            if ($user->hasRole('circle')) {
                $query->where('circle_id', $user->jurisdiction_id);
            }
            return $query->get();
        }

        return collect();
    }

    private function getSubDivisionsForFilter($user, ?string $divisionId): \Illuminate\Database\Eloquent\Collection
    {
        if ($user->hasAnyRole(['admin', 'circle'])) {
            $query = SubDivision::orderBy('name');
            if ($divisionId) {
                $query->where('division_id', $divisionId);
            } elseif ($user->hasRole('circle')) {
                $query->whereHas('division', fn($q) => $q->where('circle_id', $user->jurisdiction_id));
            }
            return $query->get();
        }

        if ($user->hasRole('division_manager')) {
            return SubDivision::where('division_id', $user->jurisdiction_id)->orderBy('name')->get();
        }

        return collect();
    }
}
