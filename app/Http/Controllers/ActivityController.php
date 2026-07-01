<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityDocument;
use App\Models\ActivityIndicator;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Renja;
use App\Models\SubActivity;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Activity::with(['program', 'unit', 'fiscalYear', 'responsibleUser']);

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

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $activities = $query->latest()->paginate(10)->withQueryString();
        $units = Unit::get(['id', 'name', 'code']);
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year']);

        return Inertia::render('activities/Index', [
            'activities' => $activities,
            'units' => $units,
            'fiscalYears' => $fiscalYears,
            'filters' => $request->only(['search', 'unit_id', 'fiscal_year_id', 'status']),
        ]);
    }

    public function create(): Response
    {
        $programs = Program::where('status', 'active')->get(['id', 'name', 'code']);
        $renjas = Renja::where('status', 'approved')->get(['id', 'title']);
        $units = Unit::where('is_active', true)->get(['id', 'name', 'code']);
        $fiscalYears = FiscalYear::where('is_locked', false)->get(['id', 'year']);
        $users = User::get(['id', 'name']);

        return Inertia::render('activities/Create', [
            'programs' => $programs,
            'renjas' => $renjas,
            'units' => $units,
            'fiscalYears' => $fiscalYears,
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:activities,code,NULL,id,fiscal_year_id,'.$request->input('fiscal_year_id'),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'program_id' => 'required|exists:programs,id',
            'renja_id' => 'nullable|exists:renjas,id',
            'unit_id' => 'required|exists:units,id',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'status' => 'required|string|in:draft,proposed,approved,in_progress,completed,cancelled',
            'priority' => 'required|string|in:low,medium,high,critical',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string',
            'sub_activities' => 'nullable|array',
            'sub_activities.*.name' => 'required|string',
            'sub_activities.*.description' => 'nullable|string',
            'sub_activities.*.status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'sub_activities.*.start_date' => 'nullable|date',
            'sub_activities.*.end_date' => 'nullable|date|after_or_equal:sub_activities.*.start_date',
            'sub_activities.*.progress_percentage' => 'required|integer|min:0|max:100',
            'sub_activities.*.assigned_to' => 'nullable|exists:users,id',
            'indicators' => 'nullable|array',
            'indicators.*.code' => 'required|string|max:255',
            'indicators.*.name' => 'required|string|max:255',
            'indicators.*.indicator_type' => 'required|string|in:iku,ikk',
            'indicators.*.target_value' => 'required|numeric|min:0',
            'indicators.*.actual_value' => 'nullable|numeric|min:0',
            'indicators.*.unit_of_measure' => 'required|string|max:255',
            'indicators.*.quarter' => 'required|string|in:Q1,Q2,Q3,Q4,annual',
        ]);

        // Check local duplicates in request indicators array
        $indicators = $request->input('indicators', []);
        $pairs = [];
        foreach ($indicators as $ind) {
            $key = $ind['code'].'-'.$ind['quarter'];
            if (in_array($key, $pairs)) {
                throw ValidationException::withMessages([
                    'indicators' => 'Kode dan periode indikator tidak boleh duplikat dalam satu kegiatan.',
                ]);
            }
            $pairs[] = $key;
        }

        $activity = Activity::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'program_id' => $validated['program_id'],
            'renja_id' => $validated['renja_id'] ?? null,
            'unit_id' => $validated['unit_id'],
            'fiscal_year_id' => $validated['fiscal_year_id'],
            'responsible_user_id' => $validated['responsible_user_id'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'progress_percentage' => 0,
            'location' => $validated['location'] ?? null,
        ]);

        if (! empty($validated['sub_activities'])) {
            foreach ($validated['sub_activities'] as $sub) {
                $activity->subActivities()->create($sub);
            }
        }

        if (! empty($validated['indicators'])) {
            foreach ($validated['indicators'] as $ind) {
                $activity->indicators()->create($ind);
            }
        }

        return redirect()->route('activities.index')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function show(Activity $activity): Response
    {
        $activity->load([
            'program',
            'unit',
            'fiscalYear',
            'responsibleUser',
            'subActivities.assignedUser',
            'budgets.realizations',
            'indicators',
            'documents.uploader',
            'approvalRequest.steps.role',
            'approvalRequest.steps.approver',
        ]);

        $users = User::get(['id', 'name']);

        return Inertia::render('activities/Show', [
            'activity' => $activity,
            'users' => $users,
        ]);
    }

    public function edit(Activity $activity): Response
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');
        if ($activity->status !== 'draft' && ! $isAdmin) {
            abort(403, 'Kegiatan yang sedang ditinjau atau telah disetujui tidak dapat diubah.');
        }

        $activity->load(['subActivities', 'indicators']);
        $programs = Program::where('status', 'active')->get(['id', 'name', 'code']);
        $renjas = Renja::where('status', 'approved')->get(['id', 'title']);
        $units = Unit::where('is_active', true)->get(['id', 'name', 'code']);
        $fiscalYears = FiscalYear::where('is_locked', false)->get(['id', 'year']);
        $users = User::get(['id', 'name']);

        return Inertia::render('activities/Edit', [
            'activity' => $activity,
            'programs' => $programs,
            'renjas' => $renjas,
            'units' => $units,
            'fiscalYears' => $fiscalYears,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');
        if ($activity->status !== 'draft' && ! $isAdmin) {
            abort(403, 'Kegiatan yang sedang ditinjau atau telah disetujui tidak dapat diubah.');
        }

        $validated = $request->validate([
            'code' => 'required|string|unique:activities,code,'.$activity->id.',id,fiscal_year_id,'.$request->input('fiscal_year_id'),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'program_id' => 'required|exists:programs,id',
            'renja_id' => 'nullable|exists:renjas,id',
            'unit_id' => 'required|exists:units,id',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'status' => 'required|string|in:draft,proposed,approved,in_progress,completed,cancelled',
            'priority' => 'required|string|in:low,medium,high,critical',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'progress_percentage' => 'required|integer|min:0|max:100',
            'location' => 'nullable|string',
            'sub_activities' => 'nullable|array',
            'sub_activities.*.id' => 'nullable|exists:sub_activities,id',
            'sub_activities.*.name' => 'required|string',
            'sub_activities.*.description' => 'nullable|string',
            'sub_activities.*.status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'sub_activities.*.start_date' => 'nullable|date',
            'sub_activities.*.end_date' => 'nullable|date|after_or_equal:sub_activities.*.start_date',
            'sub_activities.*.progress_percentage' => 'required|integer|min:0|max:100',
            'sub_activities.*.assigned_to' => 'nullable|exists:users,id',
            'indicators' => 'nullable|array',
            'indicators.*.id' => 'nullable|exists:activity_indicators,id',
            'indicators.*.code' => 'required|string|max:255',
            'indicators.*.name' => 'required|string|max:255',
            'indicators.*.indicator_type' => 'required|string|in:iku,ikk',
            'indicators.*.target_value' => 'required|numeric|min:0',
            'indicators.*.actual_value' => 'nullable|numeric|min:0',
            'indicators.*.unit_of_measure' => 'required|string|max:255',
            'indicators.*.quarter' => 'required|string|in:Q1,Q2,Q3,Q4,annual',
        ]);

        // Check local duplicates in request indicators array
        $indicators = $request->input('indicators', []);
        $pairs = [];
        foreach ($indicators as $ind) {
            $key = $ind['code'].'-'.$ind['quarter'];
            if (in_array($key, $pairs)) {
                throw ValidationException::withMessages([
                    'indicators' => 'Kode dan periode indikator tidak boleh duplikat dalam satu kegiatan.',
                ]);
            }
            $pairs[] = $key;
        }

        if (! $isAdmin && $validated['status'] !== 'draft') {
            abort(403, 'Hanya Administrator yang dapat mengubah status secara manual.');
        }

        $activity->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'program_id' => $validated['program_id'],
            'renja_id' => $validated['renja_id'] ?? null,
            'unit_id' => $validated['unit_id'],
            'fiscal_year_id' => $validated['fiscal_year_id'],
            'responsible_user_id' => $validated['responsible_user_id'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'progress_percentage' => $validated['progress_percentage'],
            'location' => $validated['location'] ?? null,
        ]);

        // Sync sub activities
        $existingIds = array_filter(array_map(fn ($sub) => $sub['id'] ?? null, $validated['sub_activities'] ?? []));
        $activity->subActivities()->whereNotIn('id', $existingIds)->delete();

        if (! empty($validated['sub_activities'])) {
            foreach ($validated['sub_activities'] as $sub) {
                if (isset($sub['id'])) {
                    SubActivity::where('id', $sub['id'])->firstOrFail()->update($sub);
                } else {
                    $activity->subActivities()->create($sub);
                }
            }
        }

        // Sync indicators
        $existingIndicatorIds = array_filter(array_map(fn ($ind) => $ind['id'] ?? null, $validated['indicators'] ?? []));
        $activity->indicators()->whereNotIn('id', $existingIndicatorIds)->delete();

        if (! empty($validated['indicators'])) {
            foreach ($validated['indicators'] as $ind) {
                if (isset($ind['id'])) {
                    ActivityIndicator::where('id', $ind['id'])->firstOrFail()->update($ind);
                } else {
                    $activity->indicators()->create($ind);
                }
            }
        }

        return redirect()->route('activities.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function uploadDocument(Request $request, Activity $activity): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,docx,xlsx,png,jpg',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('activity-documents', 'public');

        ActivityDocument::create([
            'activity_id' => $activity->id,
            'uploaded_by' => Auth::id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'description' => $request->input('description'),
        ]);

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function deleteDocument(ActivityDocument $document): RedirectResponse
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $activity->delete();

        return redirect()->route('activities.index')->with('success', 'Kegiatan berhasil dihapus.');
    }
}
