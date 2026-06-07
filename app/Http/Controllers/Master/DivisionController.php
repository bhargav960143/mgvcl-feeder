<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Circle;
use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DivisionController extends Controller
{
    public function index(Request $request): View
    {
        $user  = $request->user();
        $query = Division::with('circle')->withCount('subDivisions')->orderBy('name');

        if ($user->hasRole('circle')) {
            $query->where('circle_id', $user->jurisdiction_id);
        }

        $divisions = $query->get();
        return view('master.divisions.index', compact('divisions'));
    }

    public function create(Request $request): View
    {
        $circles = $this->getCircles($request->user());
        return view('master.divisions.create', compact('circles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'circle_id' => ['required', 'exists:circles,id'],
            'name'      => ['required', 'string', 'max:100',
                Rule::unique('divisions')->where('circle_id', $request->circle_id)],
        ]);

        // Circle user can only create in their own circle
        if ($user->hasRole('circle') && $data['circle_id'] != $user->jurisdiction_id) {
            abort(403);
        }

        Division::create($data);

        return redirect()->route('master.divisions.index')
            ->with('success', "Division [{$data['name']}] created.");
    }

    public function edit(Request $request, Division $division): View
    {
        $this->authorizeJurisdiction($request->user(), $division);
        $circles = $this->getCircles($request->user());
        return view('master.divisions.edit', compact('division', 'circles'));
    }

    public function update(Request $request, Division $division): RedirectResponse
    {
        $this->authorizeJurisdiction($request->user(), $division);

        $data = $request->validate([
            'circle_id' => ['required', 'exists:circles,id'],
            'name'      => ['required', 'string', 'max:100',
                Rule::unique('divisions')->where('circle_id', $request->circle_id)->ignore($division->id)],
        ]);

        $division->update($data);

        return redirect()->route('master.divisions.index')->with('success', 'Division updated.');
    }

    public function destroy(Request $request, Division $division): RedirectResponse
    {
        $this->authorizeJurisdiction($request->user(), $division);
        $division->delete();
        return redirect()->route('master.divisions.index')->with('success', 'Division deleted.');
    }

    private function getCircles($user)
    {
        $query = Circle::orderBy('name');
        if ($user->hasRole('circle')) {
            $query->where('id', $user->jurisdiction_id);
        }
        return $query->get();
    }

    private function authorizeJurisdiction($user, Division $division): void
    {
        if ($user->hasRole('circle') && $division->circle_id !== $user->jurisdiction_id) {
            abort(403);
        }
    }
}
