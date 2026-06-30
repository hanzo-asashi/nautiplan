<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Renja;
use App\Models\Renstra;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RenjaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Renja::with(['fiscalYear', 'renstra', 'unit', 'creator']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('fiscal_year_id')) {
            $query->where('fiscal_year_id', $request->input('fiscal_year_id'));
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->input('unit_id'));
        }

        $renjas = $query->latest()->paginate(10)->withQueryString();
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year']);
        $units = Unit::get(['id', 'name', 'code']);

        return Inertia::render('renja/Index', [
            'renjas' => $renjas,
            'fiscalYears' => $fiscalYears,
            'units' => $units,
            'filters' => $request->only(['search', 'fiscal_year_id', 'unit_id']),
        ]);
    }

    public function create(): Response
    {
        $fiscalYears = FiscalYear::where('is_locked', false)->get(['id', 'year']);
        $renstras = Renstra::where('status', 'active')->get(['id', 'title']);
        $units = Unit::where('is_active', true)->get(['id', 'name', 'code']);

        return Inertia::render('renja/Create', [
            'fiscalYears' => $fiscalYears,
            'renstras' => $renstras,
            'units' => $units,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'renstra_id' => 'nullable|exists:renstras,id',
            'unit_id' => 'required|exists:units,id',
            'status' => 'required|string|in:draft,submitted,approved,revision,archived',
            'total_budget' => 'required|numeric|min:0',
        ]);

        Renja::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('renja.index')->with('success', 'Rencana Kerja Tahunan (Renja) berhasil ditambahkan.');
    }

    public function show(Renja $renja): Response
    {
        $renja->load(['fiscalYear', 'renstra', 'unit', 'creator', 'activities.program']);

        return Inertia::render('renja/Show', [
            'renja' => $renja,
        ]);
    }

    public function edit(Renja $renja): Response
    {
        $fiscalYears = FiscalYear::where('is_locked', false)->get(['id', 'year']);
        $renstras = Renstra::where('status', 'active')->get(['id', 'title']);
        $units = Unit::where('is_active', true)->get(['id', 'name', 'code']);

        return Inertia::render('renja/Edit', [
            'renja' => $renja,
            'fiscalYears' => $fiscalYears,
            'renstras' => $renstras,
            'units' => $units,
        ]);
    }

    public function update(Request $request, Renja $renja): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'renstra_id' => 'nullable|exists:renstras,id',
            'unit_id' => 'required|exists:units,id',
            'status' => 'required|string|in:draft,submitted,approved,revision,archived',
            'total_budget' => 'required|numeric|min:0',
        ]);

        $renja->update($validated);

        return redirect()->route('renja.index')->with('success', 'Rencana Kerja Tahunan (Renja) berhasil diperbarui.');
    }

    public function destroy(Renja $renja): RedirectResponse
    {
        $renja->delete();

        return redirect()->route('renja.index')->with('success', 'Rencana Kerja Tahunan (Renja) berhasil dihapus.');
    }
}
