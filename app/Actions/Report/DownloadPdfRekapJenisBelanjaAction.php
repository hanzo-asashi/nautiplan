<?php

namespace App\Actions\Report;

use App\Models\ActivityBudget;
use App\Models\FiscalYear;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DownloadPdfRekapJenisBelanjaAction
{
    /**
     * Download the PDF report for Rekap per Jenis Belanja.
     */
    public function execute(Request $request): Response
    {
        $activeYear = FiscalYear::where('is_active', true)->first() ?? FiscalYear::orderBy('year', 'desc')->first();
        $selectedYearId = $request->input('fiscal_year_id', $activeYear?->id);
        $fiscalYear = FiscalYear::where('id', $selectedYearId)->first();

        // Get all budgets with realizations in target fiscal year
        $budgets = ActivityBudget::whereHas('activity', function ($q) use ($selectedYearId) {
            $q->where('fiscal_year_id', $selectedYearId);
        })->with('realizations')->get();

        // Map database budget_category keys to human-readable categories
        $categoriesMap = [
            'personnel' => 'Personnel / Belanja Pegawai',
            'goods_services' => 'Goods & Services / Belanja Barang & Jasa',
            'capital' => 'Capital / Belanja Modal',
            'other' => 'Other / Belanja Lain-lain',
        ];

        $categories = [];
        foreach ($categoriesMap as $key => $label) {
            $catBudgets = $budgets->where('budget_category', $key);
            $pagu = (float) $catBudgets->sum('amount');
            $realisasi = (float) $catBudgets->flatMap->realizations->sum('amount');

            $categories[] = [
                'key' => $key,
                'label' => $label,
                'pagu' => $pagu,
                'realisasi' => $realisasi,
            ];
        }

        $totalPagu = (float) array_sum(array_column($categories, 'pagu'));
        $totalRealisasi = (float) array_sum(array_column($categories, 'realisasi'));

        $pdf = Pdf::loadView('pdf.rekap-jenis-belanja', compact('categories', 'fiscalYear', 'totalPagu', 'totalRealisasi'))
            ->setPaper('a4', 'portrait');

        $yearName = $fiscalYear !== null ? (string) $fiscalYear->year : '';

        return $pdf->stream('laporan-rekap-jenis-belanja-'.$yearName.'.pdf');
    }
}
