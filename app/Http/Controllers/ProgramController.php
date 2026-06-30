<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\ProgramIndicator;
use App\Models\Renstra;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Program::with(['unit', 'fiscalYear', 'renstra']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->input('unit_id'));
        }

        if ($request->filled('fiscal_year_id')) {
            $query->where('fiscal_year_id', $request->input('fiscal_year_id'));
        }

        $programs = $query->latest()->paginate(10)->withQueryString();
        $units = Unit::get(['id', 'name', 'code']);
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year']);

        return Inertia::render('programs/Index', [
            'programs' => $programs,
            'units' => $units,
            'fiscalYears' => $fiscalYears,
            'filters' => $request->only(['search', 'unit_id', 'fiscal_year_id']),
        ]);
    }

    public function create(): Response
    {
        $renstras = Renstra::where('status', 'active')->get(['id', 'title']);
        $units = Unit::where('is_active', true)->get(['id', 'name', 'code']);
        $fiscalYears = FiscalYear::where('is_locked', false)->get(['id', 'year']);

        return Inertia::render('programs/Create', [
            'renstras' => $renstras,
            'units' => $units,
            'fiscalYears' => $fiscalYears,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:programs,code,NULL,id,fiscal_year_id,'.$request->input('fiscal_year_id'),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'renstra_id' => 'nullable|exists:renstras,id',
            'unit_id' => 'required|exists:units,id',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'objective' => 'nullable|string',
            'status' => 'required|string|in:draft,active,completed,cancelled',
            'priority' => 'required|string|in:low,medium,high,critical',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'total_budget' => 'required|numeric|min:0',
            'indicators' => 'nullable|array',
            'indicators.*.code' => 'required|string',
            'indicators.*.name' => 'required|string',
            'indicators.*.target_value' => 'required|numeric|min:0',
            'indicators.*.unit_of_measure' => 'required|string',
        ]);

        $program = Program::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'renstra_id' => $validated['renstra_id'],
            'unit_id' => $validated['unit_id'],
            'fiscal_year_id' => $validated['fiscal_year_id'],
            'objective' => $validated['objective'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_budget' => $validated['total_budget'],
            'created_by' => Auth::id(),
        ]);

        if (! empty($validated['indicators'])) {
            foreach ($validated['indicators'] as $ind) {
                $program->indicators()->create($ind);
            }
        }

        return redirect()->route('programs.index')->with('success', 'Program berhasil ditambahkan.');
    }

    public function show(Program $program): Response
    {
        $program->load(['indicators', 'unit', 'fiscalYear', 'renstra', 'activities.unit', 'creator']);

        return Inertia::render('programs/Show', [
            'program' => $program,
        ]);
    }

    public function edit(Program $program): Response
    {
        $program->load('indicators');
        $renstras = Renstra::where('status', 'active')->get(['id', 'title']);
        $units = Unit::where('is_active', true)->get(['id', 'name', 'code']);
        $fiscalYears = FiscalYear::where('is_locked', false)->get(['id', 'year']);

        return Inertia::render('programs/Edit', [
            'program' => $program,
            'renstras' => $renstras,
            'units' => $units,
            'fiscalYears' => $fiscalYears,
        ]);
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:programs,code,'.$program->id.',id,fiscal_year_id,'.$request->input('fiscal_year_id'),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'renstra_id' => 'nullable|exists:renstras,id',
            'unit_id' => 'required|exists:units,id',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'objective' => 'nullable|string',
            'status' => 'required|string|in:draft,active,completed,cancelled',
            'priority' => 'required|string|in:low,medium,high,critical',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'total_budget' => 'required|numeric|min:0',
            'indicators' => 'nullable|array',
            'indicators.*.id' => 'nullable|exists:program_indicators,id',
            'indicators.*.code' => 'required|string',
            'indicators.*.name' => 'required|string',
            'indicators.*.target_value' => 'required|numeric|min:0',
            'indicators.*.actual_value' => 'nullable|numeric|min:0',
            'indicators.*.unit_of_measure' => 'required|string',
        ]);

        // Fix potential validation typo: 'exists:fiscal_year_id,id' should be 'exists:fiscal_years,id'
        // Let's do that right here.

        $program->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'renstra_id' => $validated['renstra_id'],
            'unit_id' => $validated['unit_id'],
            'fiscal_year_id' => $request->input('fiscal_year_id'),
            'objective' => $validated['objective'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_budget' => $validated['total_budget'],
        ]);

        // Sync nested program indicators
        $existingIds = array_filter(array_map(fn ($ind) => $ind['id'] ?? null, $validated['indicators'] ?? []));
        $program->indicators()->whereNotIn('id', $existingIds)->delete();

        if (! empty($validated['indicators'])) {
            foreach ($validated['indicators'] as $ind) {
                if (isset($ind['id'])) {
                    ProgramIndicator::where('id', $ind['id'])->firstOrFail()->update($ind);
                } else {
                    $program->indicators()->create($ind);
                }
            }
        }

        return redirect()->route('programs.index')->with('success', 'Program berhasil diperbarui.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $program->delete();

        return redirect()->route('programs.index')->with('success', 'Program berhasil dihapus.');
    }
}
