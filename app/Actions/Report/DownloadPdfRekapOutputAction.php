<?php

namespace App\Actions\Report;

use App\Models\FiscalYear;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DownloadPdfRekapOutputAction
{
    protected BuildPokTreeAction $buildPokTreeAction;

    public function __construct(BuildPokTreeAction $buildPokTreeAction)
    {
        $this->buildPokTreeAction = $buildPokTreeAction;
    }

    /**
     * Download the PDF report for Rekap Output.
     */
    public function execute(Request $request): Response
    {
        $activeYear = FiscalYear::where('is_active', true)->first() ?? FiscalYear::orderBy('year', 'desc')->first();
        $selectedYearId = $request->input('fiscal_year_id', $activeYear?->id);
        $fiscalYear = FiscalYear::where('id', $selectedYearId)->first();

        $tree = $this->buildPokTreeAction->execute($selectedYearId ? (int) $selectedYearId : null);

        $totalPagu = (float) array_sum(array_column($tree, 'pagu'));
        $totalRealisasi = (float) array_sum(array_column($tree, 'realisasi'));
        $totalSisa = $totalPagu - $totalRealisasi;

        $pdf = Pdf::loadView('pdf.rekap-output', compact('tree', 'fiscalYear', 'totalPagu', 'totalRealisasi', 'totalSisa'))
            ->setPaper('a4', 'landscape');

        $yearName = $fiscalYear !== null ? (string) $fiscalYear->year : '';

        return $pdf->stream('laporan-rekap-output-'.$yearName.'.pdf');
    }
}
