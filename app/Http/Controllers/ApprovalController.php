<?php

namespace App\Http\Controllers;

use App\Mail\ApprovalStatusChanged;
use App\Models\Activity;
use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $userRoleIds = $user->roles->pluck('id')->toArray();

        // 1. Pending my action
        $pendingApprovals = ApprovalRequest::with(['approvable', 'requester', 'steps.role'])
            ->where('status', 'pending')
            ->whereHas('steps', function ($query) use ($userRoleIds) {
                $query->where('status', 'pending')
                    ->whereIn('role_id', $userRoleIds)
                    ->whereColumn('step_order', 'approval_requests.current_step');
            })
            ->get()
            ->map(fn ($req) => [
                'id' => $req->id,
                'type' => $req->approvable_type === Activity::class ? 'Kegiatan' : 'Proposal',
                'name' => $req->approvable->name ?? 'Unknown',
                'requester' => $req->requester->name ?? 'Unknown',
                'requested_at' => $req->created_at->format('d M Y H:i'),
                'current_step_order' => $req->current_step,
                'current_step_id' => $req->steps->where('step_order', $req->current_step)->first()->id ?? null,
                'role_name' => $req->steps->where('step_order', $req->current_step)->first()->role->display_name ?? null,
            ]);

        // 2. My requested history
        $myRequests = ApprovalRequest::with(['approvable', 'steps.role', 'steps.approver'])
            ->where('requested_by', $user->id)
            ->latest()
            ->get()
            ->map(fn ($req) => [
                'id' => $req->id,
                'type' => $req->approvable_type === Activity::class ? 'Kegiatan' : 'Proposal',
                'name' => $req->approvable->name ?? 'Unknown',
                'status' => $req->status,
                'notes' => $req->notes,
                'requested_at' => $req->created_at->format('d M Y H:i'),
                'steps' => $req->steps->map(fn (ApprovalStep $step) => [
                    'role_name' => $step->role->display_name,
                    'status' => $step->status,
                    'notes' => $step->notes,
                    'acted_by' => $step->approver?->name,
                    'acted_at' => $step->acted_at?->format('d M Y H:i'),
                ])->values()->all(),
            ]);

        return Inertia::render('approvals/Index', [
            'pending_approvals' => $pendingApprovals,
            'my_requests' => $myRequests,
        ]);
    }

    public function submit(Activity $activity): RedirectResponse
    {
        if ($activity->status !== 'draft') {
            abort(400, 'Kegiatan harus berstatus Draft untuk diajukan.');
        }

        $roleKepalaBagian = Role::where('name', 'kepala-bagian')->firstOrFail();
        $roleWakilDirektur = Role::where('name', 'wakil-direktur')->firstOrFail();
        $roleDirektur = Role::where('name', 'direktur')->firstOrFail();

        // Find or create approval request
        $approvalRequest = ApprovalRequest::where('approvable_type', Activity::class)
            ->where('approvable_id', $activity->id)
            ->first();

        if ($approvalRequest) {
            $approvalRequest->update([
                'current_step' => 1,
                'status' => 'pending',
                'notes' => null,
            ]);
            $approvalRequest->steps()->update([
                'status' => 'pending',
                'notes' => null,
                'approver_id' => null,
                'acted_at' => null,
            ]);
        } else {
            $approvalRequest = ApprovalRequest::create([
                'approvable_type' => Activity::class,
                'approvable_id' => $activity->id,
                'requested_by' => (int) auth()->id(),
                'current_step' => 1,
                'status' => 'pending',
            ]);

            $approvalRequest->steps()->create([
                'step_order' => 1,
                'role_id' => $roleKepalaBagian->id,
                'status' => 'pending',
            ]);

            $approvalRequest->steps()->create([
                'step_order' => 2,
                'role_id' => $roleWakilDirektur->id,
                'status' => 'pending',
            ]);

            $approvalRequest->steps()->create([
                'step_order' => 3,
                'role_id' => $roleDirektur->id,
                'status' => 'pending',
            ]);
        }

        $activity->update(['status' => 'proposed']);

        // Send email to users with the first step's role (kepala-bagian)
        $nextUsers = User::whereHas('roles', fn ($q) => $q->where('roles.id', $roleKepalaBagian->id))->get();
        foreach ($nextUsers as $user) {
            Mail::to($user->email)->send(new ApprovalStatusChanged($activity->name, 'proposed'));
        }

        // Create database notifications
        Notification::create([
            'user_id' => $activity->responsible_user_id ?: auth()->id(),
            'title' => 'Kegiatan Diajukan',
            'message' => "Kegiatan [{$activity->code}] {$activity->name} berhasil diajukan untuk persetujuan.",
            'type' => 'approval',
        ]);

        foreach ($nextUsers as $nu) {
            Notification::create([
                'user_id' => $nu->id,
                'title' => 'Persetujuan Kegiatan Baru',
                'message' => "Kegiatan [{$activity->code}] {$activity->name} diajukan dan membutuhkan persetujuan Anda.",
                'type' => 'approval',
            ]);
        }

        return back()->with('success', 'Kegiatan berhasil diajukan untuk persetujuan.');
    }

    public function action(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,revision',
            'notes' => 'nullable|string',
        ]);

        if (in_array($validated['status'], ['rejected', 'revision'])) {
            $request->validate([
                'notes' => 'required|string',
            ]);
        }

        $user = auth()->user();
        $userRoleIds = $user->roles->pluck('id')->toArray();

        // Get current step
        $step = $approvalRequest->steps()
            ->where('step_order', $approvalRequest->current_step)
            ->firstOrFail();

        // Authorize user has current step's role
        if (! in_array($step->role_id, $userRoleIds)) {
            abort(403, 'Anda tidak memiliki wewenang untuk menyetujui langkah ini.');
        }

        $notes = $validated['notes'] ?? null;

        /** @var Activity $activity */
        $activity = $approvalRequest->approvable;

        if ($validated['status'] === 'approved') {
            $step->update([
                'status' => 'approved',
                'notes' => $notes,
                'approver_id' => (int) $user->id,
                'acted_at' => now(),
            ]);

            if ($approvalRequest->current_step < 3) {
                // Advance to next step
                $approvalRequest->increment('current_step');

                // Get next step's role and users to notify
                $nextStep = $approvalRequest->steps()->where('step_order', $approvalRequest->current_step)->first();
                if ($nextStep) {
                    $nextUsers = User::whereHas('roles', fn ($q) => $q->where('roles.id', $nextStep->role_id))->get();
                    foreach ($nextUsers as $nu) {
                        Mail::to($nu->email)->send(new ApprovalStatusChanged($activity->name, 'proposed', $notes));
                    }

                    // Notify next step approvers
                    foreach ($nextUsers as $nu) {
                        Notification::create([
                            'user_id' => $nu->id,
                            'title' => 'Persetujuan Kegiatan Baru',
                            'message' => "Kegiatan [{$activity->code}] {$activity->name} membutuhkan persetujuan Anda.",
                            'type' => 'approval',
                        ]);
                    }
                }

                // Notify requester
                Notification::create([
                    'user_id' => $approvalRequest->requested_by,
                    'title' => 'Persetujuan Langkah Selesai',
                    'message' => "Langkah persetujuan {$step->step_order} untuk kegiatan [{$activity->code}] {$activity->name} telah disetujui oleh {$user->name}.",
                    'type' => 'approval',
                ]);
            } else {
                // Fully approved
                $approvalRequest->update(['status' => 'approved', 'notes' => $notes]);
                $activity->update(['status' => 'approved']);

                // Notify requester
                Mail::to($approvalRequest->requester->email)->send(new ApprovalStatusChanged($activity->name, 'approved', $notes));

                Notification::create([
                    'user_id' => $approvalRequest->requested_by,
                    'title' => 'Kegiatan Disetujui Sepenuhnya',
                    'message' => "Kegiatan [{$activity->code}] {$activity->name} telah disetujui sepenuhnya.",
                    'type' => 'approval',
                ]);
            }
        } elseif ($validated['status'] === 'rejected') {
            $step->update([
                'status' => 'rejected',
                'notes' => $notes,
                'approver_id' => (int) $user->id,
                'acted_at' => now(),
            ]);

            $approvalRequest->update(['status' => 'rejected', 'notes' => $notes]);
            $activity->update(['status' => 'cancelled']);

            // Notify requester
            Mail::to($approvalRequest->requester->email)->send(new ApprovalStatusChanged($activity->name, 'rejected', $notes));

            Notification::create([
                'user_id' => $approvalRequest->requested_by,
                'title' => 'Kegiatan Ditolak',
                'message' => "Kegiatan [{$activity->code}] {$activity->name} ditolak. Catatan: {$notes}.",
                'type' => 'approval',
            ]);
        } elseif ($validated['status'] === 'revision') {
            $step->update([
                'status' => 'pending', // Keeps step pending but logs revision request at request level
                'notes' => $notes,
                'approver_id' => (int) $user->id,
                'acted_at' => now(),
            ]);

            $approvalRequest->update(['status' => 'revision', 'notes' => $notes]);
            $activity->update(['status' => 'draft']);

            // Notify requester
            Mail::to($approvalRequest->requester->email)->send(new ApprovalStatusChanged($activity->name, 'revision', $notes));

            Notification::create([
                'user_id' => $approvalRequest->requested_by,
                'title' => 'Permintaan Revisi Kegiatan',
                'message' => "Kegiatan [{$activity->code}] {$activity->name} memerlukan revisi. Catatan: {$notes}.",
                'type' => 'approval',
            ]);
        }

        return back()->with('success', 'Tindakan persetujuan berhasil disimpan.');
    }
}
