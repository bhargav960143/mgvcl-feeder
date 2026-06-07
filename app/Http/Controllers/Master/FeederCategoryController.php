<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FeederCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeederCategoryController extends Controller
{
    public function index(): View
    {
        $categories = FeederCategory::withCount('feeders')->orderBy('name')->get();
        return view('master.feeder-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('master.feeder-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:feeder_categories', 'regex:/^[A-Z0-9_\-]+$/'],
        ]);

        FeederCategory::create(['name' => strtoupper($request->name)]);

        return redirect()->route('master.feeder-categories.index')
            ->with('success', "Category [{$request->name}] created.");
    }

    public function edit(FeederCategory $feederCategory): View
    {
        return view('master.feeder-categories.edit', compact('feederCategory'));
    }

    public function update(Request $request, FeederCategory $feederCategory): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_\-]+$/',
                Rule::unique('feeder_categories')->ignore($feederCategory->id)],
        ]);

        $old = $feederCategory->name;
        $new = strtoupper($request->name);

        $feederCategory->update(['name' => $new]);

        // Keep feeders in sync when category name changes
        if ($old !== $new) {
            \App\Models\Feeder::where('category', $old)->update(['category' => $new]);
        }

        return redirect()->route('master.feeder-categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(FeederCategory $feederCategory): RedirectResponse
    {
        if ($feederCategory->feeders()->exists()) {
            return back()->with('error', "Cannot delete [{$feederCategory->name}] — {$feederCategory->feeders()->count()} feeder(s) still use it.");
        }

        $feederCategory->delete();
        return redirect()->route('master.feeder-categories.index')
            ->with('success', 'Category deleted.');
    }
}
