<?php

namespace App\Http\Controllers;

use App\Models\ActivityBudget;
use App\Models\BudgetRealization;
use App\Models\FiscalYear;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ActivityBudget::with(['activity.unit', 'fiscalYear', 'realizations']);

        if ($request->filled('unit_id')) {
            $unitId = $request->input('unit_id');
            $query->whereHas('activity', function ($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
        }

        if ($request->filled('fiscal_year_id')) {
            $query->where('fiscal_year_id', $request->input('fiscal_year_id'));
        }

        if ($request->filled('category')) {
            $query->where('budget_category', $request->input('category'));
        }

        $budgets = $query->paginate(15)->withQueryString();

        // Calculate totals for filtering
        $totalPagu = (float) $query->sum('amount');
        $totalRealisasi = (float) BudgetRealization::whereIn(
            'activity_budget_id',
            (clone $query)->pluck('id')
        )->sum('amount');

        $units = Unit::get(['id', 'name', 'code']);
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year']);

        return Inertia::render('budgets/Index', [
            'budgets' => $budgets,
            'units' => $units,
            'fiscalYears' => $fiscalYears,
            'summary' => [
                'total_pagu' => $totalPagu,
                'total_realisasi' => $totalRealisasi,
                'sisa_anggaran' => $totalPagu - $totalRealisasi,
                'persen_realisasi' => $totalPagu > 0 ? round(($totalRealisasi / $totalPagu) * 100, 2) : 0,
            ],
            'filters' => $request->only(['unit_id', 'fiscal_year_id', 'category']),
        ]);
    }

    public function storeBudget(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'activity_id' => 'required|exists:activities,id',
            'budget_category' => 'required|string|in:personnel,goods_services,capital,other',
            'account_code' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
        ]);

        ActivityBudget::create($validated);

        return back()->with('success', 'Pagu anggaran berhasil ditambahkan.');
    }

    public function updateBudget(Request $request, ActivityBudget $budget): RedirectResponse
    {
        $validated = $request->validate([
            'budget_category' => 'required|string|in:personnel,goods_services,capital,other',
            'account_code' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        $budget->update([
            ...$validated,
            'version' => $budget->version + 1,
        ]);

        return back()->with('success', 'Pagu anggaran berhasil diperbarui.');
    }

    public function deleteBudget(ActivityBudget $budget): RedirectResponse
    {
        if ($budget->realizations()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus pagu yang telah memiliki transaksi realisasi.');
        }

        $budget->delete();

        return back()->with('success', 'Pagu anggaran berhasil dihapus.');
    }

    public function storeRealization(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'activity_budget_id' => 'required|exists:activity_budgets,id',
            'realization_type' => 'required|string|in:surat_pesanan,non_pengadaan',
            'amount' => 'required|numeric|min:0',
            'realization_date' => 'required|date',
            'description' => 'required|string|max:255',
            'receipt_number' => 'nullable|string|max:50',
            'vendor_name' => 'nullable|required_if:realization_type,surat_pesanan|string|max:255',
            'vendor_address' => 'nullable|string',
            'vendor_npwp' => 'nullable|string|max:50',
            'procurement_number' => 'nullable|required_if:realization_type,surat_pesanan|string|max:100',
            'procurement_date' => 'nullable|required_if:realization_type,surat_pesanan|date',
            'sp2d_number' => 'nullable|string|max:100',
            'sp2d_date' => 'nullable|date',
        ]);

        BudgetRealization::create([
            ...$validated,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        return back()->with('success', 'Realisasi anggaran berhasil dicatat.');
    }

    public function verifyRealization(BudgetRealization $realization): RedirectResponse
    {
        $realization->update([
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Realisasi anggaran berhasil diverifikasi.');
    }

    public function deleteRealization(BudgetRealization $realization): RedirectResponse
    {
        $realization->delete();

        return back()->with('success', 'Realisasi anggaran berhasil dihapus.');
    }
}
