<?php

namespace App\Http\Controllers;

use App\Actions\Activity\CreateActivityAction;
use App\Actions\Activity\DeleteActivityAction;
use App\Actions\Activity\DeleteActivityDocumentAction;
use App\Actions\Activity\GetActivitiesAction;
use App\Actions\Activity\GetActivityCreateDataAction;
use App\Actions\Activity\GetActivityEditDataAction;
use App\Actions\Activity\GetActivityKanbanDataAction;
use App\Actions\Activity\GetActivityRevisionsAction;
use App\Actions\Activity\GetActivityShowDataAction;
use App\Actions\Activity\UpdateActivityAction;
use App\Actions\Activity\UpdateSubActivityStatusAction;
use App\Actions\Activity\UploadActivityDocumentAction;
use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Http\Requests\Activity\UpdateSubActivityStatusRequest;
use App\Http\Requests\Activity\UploadActivityDocumentRequest;
use App\Models\Activity;
use App\Models\ActivityDocument;
use App\Models\SubActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function index(Request $request, GetActivitiesAction $action): Response
    {
        return Inertia::render('activities/Index', $action->execute($request));
    }

    public function create(GetActivityCreateDataAction $action): Response
    {
        return Inertia::render('activities/Create', $action->execute());
    }

    public function store(StoreActivityRequest $request, CreateActivityAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->route('activities.index')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function show(Activity $activity, GetActivityShowDataAction $action): Response
    {
        return Inertia::render('activities/Show', $action->execute($activity));
    }

    public function edit(Activity $activity, GetActivityEditDataAction $action): Response
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');
        if ($activity->status !== 'draft' && ! $isAdmin) {
            abort(403, 'Kegiatan yang sedang ditinjau atau telah disetujui tidak dapat diubah.');
        }

        return Inertia::render('activities/Edit', $action->execute($activity));
    }

    public function update(UpdateActivityRequest $request, Activity $activity, UpdateActivityAction $action): RedirectResponse
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');
        if ($activity->status !== 'draft' && ! $isAdmin) {
            abort(403, 'Kegiatan yang sedang ditinjau atau telah disetujui tidak dapat diubah.');
        }

        $validated = $request->validated();

        $globalRoles = ['super-admin', 'admin', 'direktur', 'wakil-direktur', 'auditor', 'staf-keuangan', 'staf-perencanaan'];
        if (! $user->isSuperAdmin() && ! $user->hasAnyRole(...$globalRoles) && $user->unit_id) {
            $validated['unit_id'] = $user->unit_id;
        }

        if (! $isAdmin && $validated['status'] !== 'draft') {
            abort(403, 'Hanya Administrator yang dapat mengubah status secara manual.');
        }

        // Action handles the DB Transaction
        $action->execute($activity, $validated);

        return redirect()->route('activities.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function uploadDocument(
        UploadActivityDocumentRequest $request,
        Activity $activity,
        UploadActivityDocumentAction $action
    ): RedirectResponse {
        $user = auth()->user();
        $isAllowed = $activity->responsible_user_id === $user->id
            || $user->unit_id === $activity->unit_id;
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');

        if (! $isAdmin && ! $isAllowed) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengunggah dokumen pada kegiatan ini.');
        }

        $action->execute($activity, $request->validated(), $request->file('file'));

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function deleteDocument(ActivityDocument $document, DeleteActivityDocumentAction $action): RedirectResponse
    {
        $user = auth()->user();
        $activity = $document->activity;
        $isAllowed = $document->uploaded_by === $user->id
            || $activity->responsible_user_id === $user->id;
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');

        if (! $isAdmin && ! $isAllowed) {
            abort(403, 'Anda tidak memiliki wewenang untuk menghapus dokumen ini.');
        }

        $action->execute($document);

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function kanban(Activity $activity, GetActivityKanbanDataAction $action): Response
    {
        $user = auth()->user();
        $isAllowed = $activity->responsible_user_id === $user->id
            || $user->unit_id === $activity->unit_id;
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');

        if (! $isAdmin && ! $isAllowed) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengakses papan Kanban kegiatan ini.');
        }

        return Inertia::render('activities/Kanban', $action->execute($activity));
    }

    public function updateSubActivityStatus(
        UpdateSubActivityStatusRequest $request,
        SubActivity $subActivity,
        UpdateSubActivityStatusAction $action
    ): RedirectResponse {
        $user = auth()->user();
        $activity = $subActivity->activity;
        $isAllowed = $subActivity->assigned_to === $user->id
            || $activity->responsible_user_id === $user->id
            || $user->unit_id === $activity->unit_id;
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');

        if (! $isAdmin && ! $isAllowed) {
            abort(403, 'Anda tidak memiliki wewenang untuk memperbarui sub-kegiatan ini.');
        }

        $action->execute($subActivity, $request->validated());

        return back()->with('success', 'Status sub-kegiatan berhasil diperbarui.');
    }

    public function revisions(Activity $activity, GetActivityRevisionsAction $action): Response
    {
        $user = auth()->user();
        $isAllowed = $activity->responsible_user_id === $user->id
            || $user->unit_id === $activity->unit_id;
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');

        if (! $isAdmin && ! $isAllowed) {
            abort(403, 'Anda tidak memiliki wewenang untuk melihat riwayat revisi kegiatan ini.');
        }

        $activity->load(['unit', 'fiscalYear']);

        $auditLogs = $action->execute($activity);

        return Inertia::render('activities/Revisions', [
            'activity' => $activity,
            'revisions' => $auditLogs,
        ]);
    }

    public function destroy(Activity $activity, DeleteActivityAction $action): RedirectResponse
    {
        $action->execute($activity);

        return redirect()->route('activities.index')->with('success', 'Kegiatan berhasil dihapus.');
    }
}
