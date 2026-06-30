<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Unit::with(['parent', 'head']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $units = $query->paginate(10)->withQueryString();

        return Inertia::render('units/Index', [
            'units' => $units,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        $parentUnits = Unit::whereNull('parent_id')->orWhere('parent_id', '!=', 0)->get(['id', 'name', 'code']);
        $users = User::get(['id', 'name']);

        return Inertia::render('units/Create', [
            'parentUnits' => $parentUnits,
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:units,code',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:units,id',
            'head_user_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    public function edit(Unit $unit): Response
    {
        $parentUnits = Unit::where('id', '!=', $unit->id)->get(['id', 'name', 'code']);
        $users = User::get(['id', 'name']);

        return Inertia::render('units/Edit', [
            'unit' => $unit,
            'parentUnits' => $parentUnits,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:units,code,'.$unit->id,
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:units,id|different:id',
            'head_user_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')->with('success', 'Unit kerja berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        // Prevent deletion if child units exist
        if ($unit->children()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus unit ini karena memiliki unit bawahan.');
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Unit kerja berhasil dihapus.');
    }
}
