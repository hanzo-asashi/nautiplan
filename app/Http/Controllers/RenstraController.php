<?php

namespace App\Http\Controllers;

use App\Models\Renstra;
use App\Models\RenstraIndicator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RenstraController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Renstra::withCount('indicators');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $renstras = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('renstra/Index', [
            'renstras' => $renstras,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('renstra/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_year' => 'required|integer|min:2020',
            'end_year' => 'required|integer|after_or_equal:start_year',
            'status' => 'required|string|in:draft,active,archived',
            'vision' => 'nullable|string',
            'mission' => 'nullable|array',
            'indicators' => 'nullable|array',
            'indicators.*.code' => 'required|string',
            'indicators.*.name' => 'required|string',
            'indicators.*.target_value' => 'required|numeric|min:0',
            'indicators.*.unit_of_measure' => 'required|string',
            'indicators.*.baseline_value' => 'required|numeric|min:0',
        ]);

        $renstra = Renstra::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_year' => $validated['start_year'],
            'end_year' => $validated['end_year'],
            'status' => $validated['status'],
            'vision' => $validated['vision'],
            'mission' => $validated['mission'] ?? [],
            'created_by' => Auth::id(),
        ]);

        if (! empty($validated['indicators'])) {
            foreach ($validated['indicators'] as $ind) {
                $renstra->indicators()->create($ind);
            }
        }

        return redirect()->route('renstra.index')->with('success', 'Rencana Strategis berhasil ditambahkan.');
    }

    public function show(Renstra $renstra): Response
    {
        $renstra->load(['indicators', 'creator']);

        return Inertia::render('renstra/Show', [
            'renstra' => $renstra,
        ]);
    }

    public function edit(Renstra $renstra): Response
    {
        $renstra->load('indicators');

        return Inertia::render('renstra/Edit', [
            'renstra' => $renstra,
        ]);
    }

    public function update(Request $request, Renstra $renstra): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_year' => 'required|integer|min:2020',
            'end_year' => 'required|integer|after_or_equal:start_year',
            'status' => 'required|string|in:draft,active,archived',
            'vision' => 'nullable|string',
            'mission' => 'nullable|array',
            'indicators' => 'nullable|array',
            'indicators.*.id' => 'nullable|exists:renstra_indicators,id',
            'indicators.*.code' => 'required|string',
            'indicators.*.name' => 'required|string',
            'indicators.*.target_value' => 'required|numeric|min:0',
            'indicators.*.unit_of_measure' => 'required|string',
            'indicators.*.baseline_value' => 'required|numeric|min:0',
        ]);

        $renstra->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_year' => $validated['start_year'],
            'end_year' => $validated['end_year'],
            'status' => $validated['status'],
            'vision' => $validated['vision'],
            'mission' => $validated['mission'] ?? [],
        ]);

        // Dynamic sync indicators
        $existingIds = array_filter(array_map(fn ($ind) => $ind['id'] ?? null, $validated['indicators'] ?? []));
        $renstra->indicators()->whereNotIn('id', $existingIds)->delete();

        if (! empty($validated['indicators'])) {
            foreach ($validated['indicators'] as $ind) {
                if (isset($ind['id'])) {
                    RenstraIndicator::where('id', $ind['id'])->firstOrFail()->update($ind);
                } else {
                    $renstra->indicators()->create($ind);
                }
            }
        }

        return redirect()->route('renstra.index')->with('success', 'Rencana Strategis berhasil diperbarui.');
    }

    public function destroy(Renstra $renstra): RedirectResponse
    {
        $renstra->delete();

        return redirect()->route('renstra.index')->with('success', 'Rencana Strategis berhasil dihapus.');
    }
}
