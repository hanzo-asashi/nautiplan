<?php

namespace App\Actions\Report;

use App\Models\BudgetRevision;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class DownloadPdfRevisionAction
{
    /**
     * Download the PDF report for Budget Revision.
     */
    public function execute(BudgetRevision $revision): Response
    {
        $revision->load([
            'activityBudget.activity.program',
            'activityBudget.activity.unit',
            'activityBudget.activity.fiscalYear',
            'details',
            'revisedBy',
        ]);

        $pdf = Pdf::loadView('pdf.revision-report', compact('revision'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("laporan-revisi-POK-rev-{$revision->revision_number}.pdf");
    }
}
