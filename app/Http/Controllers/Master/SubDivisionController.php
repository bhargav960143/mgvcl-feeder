<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\SubDivision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubDivisionController extends Controller
{
    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = SubDivision::with('division.circle')->withCount('substations')->orderBy('name');

        if ($user->hasRole('circle')) {
            $query->whereHas('division', fn($q) => $q->where('circle_id', $user->jurisdiction_id));
        }

        $subDivisions = $query->get();
        return view('master.sub-divisions.index', compact('subDivisions'));
    }

    public function create(Request $request): View
    {
        $divisions = $this->getDivisions($request->user());
        return view('master.sub-divisions.create', compact('divisions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'name'        => ['required', 'string', 'max:100',
                Rule::unique('sub_divisions')->where('division_id', $request->division_id)],
        ]);

        $this->checkDivisionAccess($request->user(), $data['division_id']);

        SubDivision::create($data);

        return redirect()->route('master.sub-divisions.index')
            ->with('success', "Sub Division [{$data['name']}] created.");
    }

    public function edit(Request $request, SubDivision $subDivision): View
    {
        $this->checkDivisionAccess($request->user(), $subDivision->division_id);
        $divisions = $this->getDivisions($request->user());
        return view('master.sub-divisions.edit', compact('subDivision', 'divisions'));
    }

    public function update(Request $request, SubDivision $subDivision): RedirectResponse
    {
        $this->checkDivisionAccess($request->user(), $subDivision->division_id);

        $data = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'name'        => ['required', 'string', 'max:100',
                Rule::unique('sub_divisions')->where('division_id', $request->division_id)->ignore($subDivision->id)],
        ]);

        $subDivision->update($data);

        return redirect()->route('master.sub-divisions.index')->with('success', 'Sub Division updated.');
    }

    public function destroy(Request $request, SubDivision $subDivision): RedirectResponse
    {
        $this->checkDivisionAccess($request->user(), $subDivision->division_id);
        $subDivision->delete();
        return redirect()->route('master.sub-divisions.index')->with('success', 'Sub Division deleted.');
    }

    private function getDivisions($user)
    {
        $query = Division::orderBy('name');
        if ($user->hasRole('circle')) {
            $query->where('circle_id', $user->jurisdiction_id);
        }
        return $query->get();
    }

    private function checkDivisionAccess($user, int $divisionId): void
    {
        if ($user->hasRole('circle')) {
            $division = Division::findOrFail($divisionId);
            if ($division->circle_id !== $user->jurisdiction_id) {
                abort(403);
            }
        }
    }
}
