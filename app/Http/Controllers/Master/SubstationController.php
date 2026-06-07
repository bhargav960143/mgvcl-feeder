<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\SubDivision;
use App\Models\Substation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubstationController extends Controller
{
    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = Substation::with('subDivision.division')->withCount('feeders')->orderBy('name');

        if ($user->hasRole('circle')) {
            $query->whereIn('sub_division_id', SubDivision::whereIn(
                'division_id',
                Division::where('circle_id', $user->jurisdiction_id)->pluck('id')
            )->pluck('id'));
        }

        if ($request->filled('sub_division_id')) {
            $query->where('sub_division_id', $request->sub_division_id);
        }

        $substations  = $query->get();
        $subDivisions = $this->getSubDivisions($user);

        return view('master.substations.index', compact('substations', 'subDivisions'));
    }

    public function create(Request $request): View
    {
        $subDivisions = $this->getSubDivisions($request->user());
        return view('master.substations.create', compact('subDivisions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sub_division_id' => ['required', 'exists:sub_divisions,id'],
            'name'            => ['required', 'string', 'max:150',
                Rule::unique('substations')->where('sub_division_id', $request->sub_division_id)],
        ]);

        $this->checkSubDivisionAccess($request->user(), $data['sub_division_id']);

        Substation::create($data);

        return redirect()->route('master.substations.index')
            ->with('success', "Substation [{$data['name']}] created.");
    }

    public function edit(Request $request, Substation $substation): View
    {
        $this->checkSubDivisionAccess($request->user(), $substation->sub_division_id);
        $subDivisions = $this->getSubDivisions($request->user());
        return view('master.substations.edit', compact('substation', 'subDivisions'));
    }

    public function update(Request $request, Substation $substation): RedirectResponse
    {
        $this->checkSubDivisionAccess($request->user(), $substation->sub_division_id);

        $data = $request->validate([
            'sub_division_id' => ['required', 'exists:sub_divisions,id'],
            'name'            => ['required', 'string', 'max:150',
                Rule::unique('substations')->where('sub_division_id', $request->sub_division_id)->ignore($substation->id)],
        ]);

        $substation->update($data);

        return redirect()->route('master.substations.index')->with('success', 'Substation updated.');
    }

    public function destroy(Request $request, Substation $substation): RedirectResponse
    {
        $this->checkSubDivisionAccess($request->user(), $substation->sub_division_id);
        $substation->delete();
        return redirect()->route('master.substations.index')->with('success', 'Substation deleted.');
    }

    private function getSubDivisions($user)
    {
        $query = SubDivision::with('division')->orderBy('name');
        if ($user->hasRole('circle')) {
            $query->whereIn('division_id', Division::where('circle_id', $user->jurisdiction_id)->pluck('id'));
        }
        return $query->get();
    }

    private function checkSubDivisionAccess($user, int $subDivisionId): void
    {
        if ($user->hasRole('circle')) {
            $sd = SubDivision::with('division')->findOrFail($subDivisionId);
            if ($sd->division->circle_id !== $user->jurisdiction_id) {
                abort(403);
            }
        }
    }
}
