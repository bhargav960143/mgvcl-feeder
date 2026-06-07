<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Feeder;
use App\Models\FeederCategory;
use App\Models\SubDivision;
use App\Models\Substation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeederMasterController extends Controller
{
    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = Feeder::with('substation.subDivision.division')->orderBy('name');

        if ($user->isCircleScoped()) {
            $query->whereIn('substation_id', Substation::whereIn(
                'sub_division_id',
                SubDivision::whereIn(
                    'division_id',
                    Division::where('circle_id', $user->jurisdiction_id)->pluck('id')
                )->pluck('id')
            )->pluck('id'));
        }

        if ($request->filled('substation_id')) {
            $query->where('substation_id', $request->substation_id);
        }

        $feeders     = $query->get();
        $substations = $this->getSubstations($user);
        $categories  = Cache::remember('feeder_categories', 3600, fn() => FeederCategory::orderBy('name')->pluck('name'));

        return view('master.feeders.index', compact('feeders', 'substations', 'categories'));
    }

    public function create(Request $request): View
    {
        $substations = $this->getSubstations($request->user());
        $categories  = Cache::remember('feeder_categories', 3600, fn() => FeederCategory::orderBy('name')->pluck('name'));
        return view('master.feeders.create', compact('substations', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'substation_id'  => ['required', 'exists:substations,id'],
            'name'           => ['required', 'string', 'max:150'],
            'tnd_code'       => ['required', 'string', 'max:20', 'unique:feeders'],
            'category'       => ['required', 'exists:feeder_categories,name'],
            'total_consumer' => ['required', 'integer', 'min:0'],
            'total_tc'       => ['required', 'integer', 'min:0'],
        ]);

        $this->checkSubstationAccess($request->user(), $data['substation_id']);

        Feeder::create($data);

        return redirect()->route('master.feeders.index')
            ->with('success', "Feeder [{$data['name']}] created.");
    }

    public function edit(Request $request, Feeder $feeder): View
    {
        $this->checkFeederAccess($request->user(), $feeder);
        $substations = $this->getSubstations($request->user());
        $categories  = Cache::remember('feeder_categories', 3600, fn() => FeederCategory::orderBy('name')->pluck('name'));
        return view('master.feeders.edit', compact('feeder', 'substations', 'categories'));
    }

    public function update(Request $request, Feeder $feeder): RedirectResponse
    {
        $this->checkFeederAccess($request->user(), $feeder);

        $data = $request->validate([
            'substation_id'  => ['required', 'exists:substations,id'],
            'name'           => ['required', 'string', 'max:150'],
            'tnd_code'       => ['required', 'string', 'max:20', Rule::unique('feeders')->ignore($feeder->id)],
            'category'       => ['required', 'exists:feeder_categories,name'],
            'total_consumer' => ['required', 'integer', 'min:0'],
            'total_tc'       => ['required', 'integer', 'min:0'],
        ]);

        $feeder->update($data);

        return redirect()->route('master.feeders.index')->with('success', 'Feeder updated.');
    }

    public function destroy(Request $request, Feeder $feeder): RedirectResponse
    {
        $this->checkFeederAccess($request->user(), $feeder);
        $feeder->delete();
        return redirect()->route('master.feeders.index')->with('success', 'Feeder deleted.');
    }

    private function getSubstations($user)
    {
        $query = Substation::with('subDivision.division')->orderBy('name');
        if ($user->isCircleScoped()) {
            $query->whereHas('subDivision.division', fn($q) =>
                $q->where('circle_id', $user->jurisdiction_id)
            );
        }
        return $query->get();
    }

    private function checkSubstationAccess($user, int $substationId): void
    {
        if ($user->isCircleScoped()) {
            $ss = Substation::with('subDivision.division')->findOrFail($substationId);
            if ($ss->subDivision->division->circle_id !== $user->jurisdiction_id) {
                abort(403);
            }
        }
    }

    private function checkFeederAccess($user, Feeder $feeder): void
    {
        if ($user->isCircleScoped()) {
            $feeder->load('substation.subDivision.division');
            if ($feeder->substation->subDivision->division->circle_id !== $user->jurisdiction_id) {
                abort(403);
            }
        }
    }
}
