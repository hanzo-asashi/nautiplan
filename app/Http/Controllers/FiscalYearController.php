<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FiscalYearController extends Controller
{
    public function index(Request $request): Response
    {
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->paginate(10);

        return Inertia::render('fiscal-years/Index', [
            'fiscalYears' => $fiscalYears,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('fiscal-years/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100|unique:fiscal_years,year',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|boolean',
            'is_locked' => 'required|boolean',
        ]);

        // If making active, deactivate all other years
        if ($validated['is_active']) {
            FiscalYear::where('is_active', true)->update(['is_active' => false]);
        }

        FiscalYear::create($validated);

        return redirect()->route('fiscal-years.index')->with('success', 'Tahun anggaran berhasil ditambahkan.');
    }

    public function edit(FiscalYear $fiscalYear): Response
    {
        return Inertia::render('fiscal-years/Edit', [
            'fiscalYear' => $fiscalYear,
        ]);
    }

    public function update(Request $request, FiscalYear $fiscalYear): RedirectResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100|unique:fiscal_years,year,'.$fiscalYear->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|boolean',
            'is_locked' => 'required|boolean',
        ]);

        // If making active, deactivate all other years
        if ($validated['is_active']) {
            FiscalYear::where('id', '!=', $fiscalYear->id)->where('is_active', true)->update(['is_active' => false]);
        }

        $fiscalYear->update($validated);

        return redirect()->route('fiscal-years.index')->with('success', 'Tahun anggaran berhasil diperbarui.');
    }

    public function toggleActive(FiscalYear $fiscalYear): RedirectResponse
    {
        if ($fiscalYear->is_active) {
            $fiscalYear->update(['is_active' => false]);
        } else {
            FiscalYear::where('is_active', true)->update(['is_active' => false]);
            $fiscalYear->update(['is_active' => true]);
        }

        return back()->with('success', 'Status keaktifan tahun anggaran berhasil diubah.');
    }

    public function toggleLock(FiscalYear $fiscalYear): RedirectResponse
    {
        $fiscalYear->update(['is_locked' => ! $fiscalYear->is_locked]);

        return back()->with('success', $fiscalYear->is_locked ? 'Tahun anggaran berhasil dikunci.' : 'Tahun anggaran berhasil dibuka kunci.');
    }

    public function destroy(FiscalYear $fiscalYear): RedirectResponse
    {
        // Avoid deleting active or locked year
        if ($fiscalYear->is_active || $fiscalYear->is_locked) {
            return back()->with('error', 'Tidak dapat menghapus tahun anggaran yang sedang aktif atau dikunci.');
        }

        $fiscalYear->delete();

        return redirect()->route('fiscal-years.index')->with('success', 'Tahun anggaran berhasil dihapus.');
    }
}
