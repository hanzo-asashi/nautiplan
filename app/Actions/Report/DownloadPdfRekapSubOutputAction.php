<?php

namespace App\Actions\Report;

use App\Models\FiscalYear;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DownloadPdfRekapSubOutputAction
{
    protected BuildPokTreeAction $buildPokTreeAction;

    public function __construct(BuildPokTreeAction $buildPokTreeAction)
    {
        $this->buildPokTreeAction = $buildPokTreeAction;
    }

    /**
     * Download the PDF report for Rekap Sub Output.
     */
    public function execute(Request $request): Response
    {
        $activeYear = FiscalYear::where('is_active', true)->first() ?? FiscalYear::orderBy('year', 'desc')->first();
        $selectedYearId = $request->input('fiscal_year_id', $activeYear?->id);
        $fiscalYear = FiscalYear::where('id', $selectedYearId)->first();

        $tree = $this->buildPokTreeAction->execute($selectedYearId ? (int) $selectedYearId : null);

        // Gather all sub outputs flat list
        $subOutputs = [];
        foreach ($tree as $program) {
            foreach ($program['children'] as $activity) {
                foreach ($activity['children'] as $output) {
                    foreach ($output['children'] as $subOutput) {
                        $subOutputs[] = [
                            'code' => $subOutput['code'],
                            'name' => $subOutput['name'],
                            'pagu' => $subOutput['pagu'],
                            'realisasi' => $subOutput['realisasi'],
                        ];
                    }
                }
            }
        }

        $totalPagu = (float) array_sum(array_column($subOutputs, 'pagu'));
        $totalRealisasi = (float) array_sum(array_column($subOutputs, 'realisasi'));

        $pdf = Pdf::loadView('pdf.rekap-sub-output', compact('subOutputs', 'fiscalYear', 'totalPagu', 'totalRealisasi'))
            ->setPaper('a4', 'portrait');

        $yearName = $fiscalYear !== null ? (string) $fiscalYear->year : '';

        return $pdf->stream('laporan-rekap-sub-output-'.$yearName.'.pdf');
    }
}
