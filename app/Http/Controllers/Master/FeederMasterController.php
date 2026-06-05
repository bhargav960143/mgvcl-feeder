<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Feeder;
use App\Models\Substation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeederMasterController extends Controller
{
    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = Feeder::with('substation.subDivision.division')->orderBy('name');

        if ($user->hasRole('circle')) {
            $query->whereHas('substation.subDivision.division', fn($q) =>
                $q->where('circle_id', $user->jurisdiction_id)
            );
        }

        if ($request->filled('substation_id')) {
            $query->where('substation_id', $request->substation_id);
        }

        $feeders     = $query->get();
        $substations = $this->getSubstations($user);
        $categories  = ['URBAN', 'GIDC', 'HTEX', 'EHT', 'SST', 'IND'];

        return view('master.feeders.index', compact('feeders', 'substations', 'categories'));
    }

    public function create(Request $request): View
    {
        $substations = $this->getSubstations($request->user());
        $categories  = ['URBAN', 'GIDC', 'HTEX', 'EHT', 'SST', 'IND'];
        return view('master.feeders.create', compact('substations', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'substation_id'  => ['required', 'exists:substations,id'],
            'name'           => ['required', 'string', 'max:150'],
            'tnd_code'       => ['required', 'string', 'max:20', 'unique:feeders'],
            'category'       => ['required', Rule::in(['URBAN', 'GIDC', 'HTEX', 'EHT', 'SST', 'IND'])],
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
        $categories  = ['URBAN', 'GIDC', 'HTEX', 'EHT', 'SST', 'IND'];
        return view('master.feeders.edit', compact('feeder', 'substations', 'categories'));
    }

    public function update(Request $request, Feeder $feeder): RedirectResponse
    {
        $this->checkFeederAccess($request->user(), $feeder);

        $data = $request->validate([
            'substation_id'  => ['required', 'exists:substations,id'],
            'name'           => ['required', 'string', 'max:150'],
            'tnd_code'       => ['required', 'string', 'max:20', Rule::unique('feeders')->ignore($feeder->id)],
            'category'       => ['required', Rule::in(['URBAN', 'GIDC', 'HTEX', 'EHT', 'SST', 'IND'])],
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
        if ($user->hasRole('circle')) {
            $query->whereHas('subDivision.division', fn($q) =>
                $q->where('circle_id', $user->jurisdiction_id)
            );
        }
        return $query->get();
    }

    private function checkSubstationAccess($user, int $substationId): void
    {
        if ($user->hasRole('circle')) {
            $ss = Substation::with('subDivision.division')->findOrFail($substationId);
            if ($ss->subDivision->division->circle_id !== $user->jurisdiction_id) {
                abort(403);
            }
        }
    }

    private function checkFeederAccess($user, Feeder $feeder): void
    {
        if ($user->hasRole('circle')) {
            $feeder->load('substation.subDivision.division');
            if ($feeder->substation->subDivision->division->circle_id !== $user->jurisdiction_id) {
                abort(403);
            }
        }
    }
}
