<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Circle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CircleController extends Controller
{
    public function index(): View
    {
        $circles = Circle::withCount('divisions')->orderBy('name')->paginate(20);
        return view('admin.circles.index', compact('circles'));
    }

    public function create(): View
    {
        return view('admin.circles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:circles']]);

        Circle::create(['name' => $request->name]);

        return redirect()->route('admin.circles.index')
            ->with('success', "Circle [{$request->name}] created.");
    }

    public function edit(Circle $circle): View
    {
        return view('admin.circles.edit', compact('circle'));
    }

    public function update(Request $request, Circle $circle): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', "unique:circles,name,{$circle->id}"],
        ]);

        $circle->update(['name' => $request->name]);

        return redirect()->route('admin.circles.index')
            ->with('success', "Circle updated.");
    }

    public function destroy(Circle $circle): RedirectResponse
    {
        $circle->delete();
        return redirect()->route('admin.circles.index')->with('success', 'Circle deleted.');
    }
}
