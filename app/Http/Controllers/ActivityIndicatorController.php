<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityIndicator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityIndicatorController extends Controller
{
    public function store(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('activity_indicators')->where(function ($query) use ($activity, $request) {
                    return $query->where('activity_id', $activity->id)
                        ->where('quarter', $request->input('quarter'));
                }),
            ],
            'name' => 'required|string|max:255',
            'indicator_type' => 'required|string|in:iku,ikk',
            'target_value' => 'required|numeric|min:0',
            'actual_value' => 'nullable|numeric|min:0',
            'unit_of_measure' => 'required|string|max:255',
            'quarter' => 'required|string|in:Q1,Q2,Q3,Q4,annual',
        ], [
            'code.unique' => 'Indikator dengan kode ini sudah ada untuk triwulan yang sama pada kegiatan ini.',
        ]);

        $activity->indicators()->create($validated);

        return back()->with('success', 'Indikator kinerja berhasil ditambahkan.');
    }

    public function update(Request $request, ActivityIndicator $indicator): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('activity_indicators')->where(function ($query) use ($indicator, $request) {
                    return $query->where('activity_id', $indicator->activity_id)
                        ->where('quarter', $request->input('quarter'));
                })->ignore($indicator->id),
            ],
            'name' => 'required|string|max:255',
            'indicator_type' => 'required|string|in:iku,ikk',
            'target_value' => 'required|numeric|min:0',
            'actual_value' => 'nullable|numeric|min:0',
            'unit_of_measure' => 'required|string|max:255',
            'quarter' => 'required|string|in:Q1,Q2,Q3,Q4,annual',
        ], [
            'code.unique' => 'Indikator dengan kode ini sudah ada untuk triwulan yang sama pada kegiatan ini.',
        ]);

        $indicator->update($validated);

        return back()->with('success', 'Indikator kinerja berhasil diperbarui.');
    }

    public function destroy(ActivityIndicator $indicator): RedirectResponse
    {
        $indicator->delete();

        return back()->with('success', 'Indikator kinerja berhasil dihapus.');
    }
}
