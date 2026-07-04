<?php

namespace App\Http\Controllers;

use App\Actions\Report\DownloadActivityTemplateAction;
use App\Actions\Report\DownloadPdfRekapKomponenAction;
use App\Actions\Report\DownloadPdfRekapOutputAction;
use App\Actions\Report\DownloadPdfRevisionAction;
use App\Actions\Report\ExportActivityExcelAction;
use App\Actions\Report\ExportPokRealizationExcelAction;
use App\Actions\Report\GetAnalyticsDataAction;
use App\Actions\Report\GetCalendarDataAction;
use App\Actions\Report\GetGanttChartDataAction;
use App\Actions\Report\GetPokMonitoringDataAction;
use App\Actions\Report\ImportActivityExcelAction;
use App\Helpers\FormatHelper;
use App\Models\Activity;
use App\Models\BudgetRealization;
use App\Models\BudgetRevision;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function gantt(Request $request, GetGanttChartDataAction $action): Response
    {
        return Inertia::render('reports/Gantt', $action->execute($request));
    }

    public function analytics(Request $request, GetAnalyticsDataAction $action): Response
    {
        return Inertia::render('reports/Analytics', $action->execute($request));
    }

    public function exportExcel(Request $request, ExportActivityExcelAction $action): StreamedResponse
    {
        return $action->execute($request);
    }

    public function downloadTemplate(DownloadActivityTemplateAction $action): StreamedResponse
    {
        return $action->execute();
    }

    public function importExcel(Request $request, ImportActivityExcelAction $action): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,bin,ods',
        ]);

        $result = $action->execute($request);

        if (isset($result['fileError'])) {
            return back()->withErrors(['file' => $result['fileError']]);
        }

        if (isset($result['errors'])) {
            return back()->with('importErrors', $result['errors']);
        }

        return back()->with('success', $result['success'] ?? 'Berhasil.');
    }

    public function downloadPdfActivity(Activity $activity): \Illuminate\Http\Response
    {
        $activity->load([
            'program',
            'unit',
            'fiscalYear',
            'responsibleUser',
            'subActivities.assignedUser',
            'budgets.realizations',
            'indicators',
            'approvalRequest.steps.role',
            'approvalRequest.steps.approver',
        ]);

        $pdf = Pdf::loadView('pdf.activity-detail', compact('activity'));

        return $pdf->stream("detail-kegiatan-{$activity->code}.pdf");
    }

    public function downloadPdfQuarterly(Activity $activity, string $quarter): \Illuminate\Http\Response
    {
        $activity->load([
            'unit',
            'fiscalYear',
            'responsibleUser',
            'indicators',
        ]);

        $report = $activity->reports()
            ->where('quarter', $quarter)
            ->with(['submittedBy', 'reviewedBy'])
            ->first();

        $pdf = Pdf::loadView('pdf.quarterly-report', compact('activity', 'report', 'quarter'));

        return $pdf->stream("laporan-monev-{$activity->code}-{$quarter}.pdf");
    }

    public function downloadPdfRealization(BudgetRealization $realization): \Illuminate\Http\Response
    {
        $realization->load([
            'activityBudget.activity.program',
            'activityBudget.activity.unit',
            'activityBudget.activity.fiscalYear',
            'procurement.vendor',
            'procurement.ppk',
            'procurement.kpa',
            'items',
        ]);

        $terbilang = FormatHelper::terbilang($realization->amount).' rupiah';
        $pdf = Pdf::loadView('pdf.surat-pesanan', compact('realization', 'terbilang'));

        return $pdf->stream("surat-pesanan-{$realization->receipt_number}.pdf");
    }

    public function downloadPdfNonProcurement(Request $request): \Illuminate\Http\Response
    {
        $realizations = BudgetRealization::where('realization_type', 'non_pengadaan')
            ->with(['activityBudget.activity.program', 'activityBudget.activity.unit', 'items'])
            ->get();

        $pdf = Pdf::loadView('pdf.laporan-non-pengadaan', compact('realizations'));

        return $pdf->stream('laporan-realisasi-non-pengadaan.pdf');
    }

    public function downloadPdfVendor(Request $request): \Illuminate\Http\Response
    {
        $realizations = BudgetRealization::where('realization_type', 'surat_pesanan')
            ->whereHas('procurement.vendor')
            ->with(['activityBudget.activity.program', 'activityBudget.activity.unit', 'procurement.vendor'])
            ->get()
            ->groupBy(function ($item) {
                return $item->procurement->vendor->name;
            });

        $pdf = Pdf::loadView('pdf.laporan-vendor', compact('realizations'));

        return $pdf->stream('laporan-realisasi-per-vendor.pdf');
    }

    public function downloadPdfSpk(BudgetRealization $realization): \Illuminate\Http\Response
    {
        $realization->load([
            'activityBudget.activity.program',
            'activityBudget.activity.unit',
            'activityBudget.activity.fiscalYear',
            'procurement.vendor',
            'procurement.ppk',
            'procurement.kpa',
            'items',
        ]);

        $terbilang = FormatHelper::terbilang($realization->amount).' rupiah';
        $pdf = Pdf::loadView('pdf.spk', compact('realization', 'terbilang'));

        return $pdf->stream("spk-{$realization->receipt_number}.pdf");
    }

    public function downloadPdfBast(BudgetRealization $realization): \Illuminate\Http\Response
    {
        $realization->load([
            'activityBudget.activity.program',
            'activityBudget.activity.unit',
            'activityBudget.activity.fiscalYear',
            'procurement.vendor',
            'procurement.ppk',
            'procurement.kpa',
            'items',
        ]);

        $terbilang = FormatHelper::terbilang($realization->amount).' rupiah';
        $pdf = Pdf::loadView('pdf.bast', compact('realization', 'terbilang'));

        return $pdf->stream("bast-{$realization->receipt_number}.pdf");
    }

    public function downloadPdfBap(BudgetRealization $realization): \Illuminate\Http\Response
    {
        $realization->load([
            'activityBudget.activity.program',
            'activityBudget.activity.unit',
            'activityBudget.activity.fiscalYear',
            'procurement.vendor',
            'procurement.ppk',
            'procurement.kpa',
            'items',
        ]);

        $terbilang = FormatHelper::terbilang($realization->amount).' rupiah';
        $pdf = Pdf::loadView('pdf.bap', compact('realization', 'terbilang'));

        return $pdf->stream("bap-{$realization->receipt_number}.pdf");
    }

    public function downloadPdfKwitansi(BudgetRealization $realization): \Illuminate\Http\Response
    {
        $realization->load([
            'activityBudget.activity.program',
            'activityBudget.activity.unit',
            'activityBudget.activity.fiscalYear',
            'procurement.vendor',
            'procurement.ppk',
            'procurement.kpa',
            'items',
        ]);

        $terbilang = FormatHelper::terbilang($realization->amount).' rupiah';
        $pdf = Pdf::loadView('pdf.kwitansi', compact('realization', 'terbilang'));

        return $pdf->stream("kwitansi-{$realization->receipt_number}.pdf");
    }

    public function downloadPdfSpp(BudgetRealization $realization): \Illuminate\Http\Response
    {
        $realization->load([
            'activityBudget.activity.program',
            'activityBudget.activity.unit',
            'activityBudget.activity.fiscalYear',
            'procurement.vendor',
            'procurement.ppk',
            'procurement.kpa',
            'items',
        ]);

        $terbilang = FormatHelper::terbilang($realization->amount).' rupiah';
        $pdf = Pdf::loadView('pdf.spp', compact('realization', 'terbilang'));

        return $pdf->stream('spp-'.($realization->spp_number ?? 'draft').'.pdf');
    }

    public function downloadPdfSpm(BudgetRealization $realization): \Illuminate\Http\Response
    {
        $realization->load([
            'activityBudget.activity.program',
            'activityBudget.activity.unit',
            'activityBudget.activity.fiscalYear',
            'procurement.vendor',
            'procurement.ppk',
            'procurement.kpa',
            'items',
        ]);

        $terbilang = FormatHelper::terbilang($realization->amount).' rupiah';
        $pdf = Pdf::loadView('pdf.spm', compact('realization', 'terbilang'));

        return $pdf->stream('spm-'.($realization->spm_number ?? 'draft').'.pdf');
    }

    public function downloadPdfSptjb(BudgetRealization $realization): \Illuminate\Http\Response
    {
        $realization->load([
            'activityBudget.activity.program',
            'activityBudget.activity.unit',
            'activityBudget.activity.fiscalYear',
            'procurement.vendor',
            'procurement.ppk',
            'procurement.kpa',
            'items',
        ]);

        $terbilang = FormatHelper::terbilang($realization->amount).' rupiah';
        $pdf = Pdf::loadView('pdf.sptjb', compact('realization', 'terbilang'));

        return $pdf->stream('sptjb-'.($realization->sptjb_number ?? 'draft').'.pdf');
    }

    public function downloadPdfSsp(BudgetRealization $realization, Request $request): \Illuminate\Http\Response
    {
        $realization->load([
            'activityBudget.activity.program',
            'activityBudget.activity.unit',
            'activityBudget.activity.fiscalYear',
            'procurement.vendor',
            'procurement.ppk',
            'procurement.kpa',
            'items',
        ]);

        $taxType = $request->query('type', 'ppn');
        $taxAmount = 0.0;
        foreach ($realization->items as $item) {
            if ($taxType === 'ppn') {
                $taxAmount += (float) $item->tax_ppn;
            } elseif ($taxType === 'pph22') {
                $taxAmount += (float) $item->tax_pph22;
            } elseif ($taxType === 'pph23') {
                $taxAmount += (float) $item->tax_pph23;
            }
        }

        $terbilang = FormatHelper::terbilang($taxAmount).' rupiah';
        $pdf = Pdf::loadView('pdf.ssp', compact('realization', 'taxType', 'taxAmount', 'terbilang'));

        return $pdf->stream("ssp-{$taxType}-".($realization->receipt_number ?? 'draft').'.pdf');
    }

    public function calendar(Request $request, GetCalendarDataAction $action): Response
    {
        return Inertia::render('reports/Calendar', $action->execute($request));
    }

    public function pokMonitoring(Request $request, GetPokMonitoringDataAction $action): Response
    {
        return Inertia::render('reports/PokMonitoring', $action->execute($request));
    }

    public function exportPokRealizationExcel(Request $request, ExportPokRealizationExcelAction $action): StreamedResponse
    {
        return $action->execute($request);
    }

    public function downloadPdfRekapOutput(Request $request, DownloadPdfRekapOutputAction $action): \Illuminate\Http\Response
    {
        return $action->execute($request);
    }

    public function downloadPdfRekapKomponen(Request $request, DownloadPdfRekapKomponenAction $action): \Illuminate\Http\Response
    {
        return $action->execute($request);
    }

    public function downloadPdfRevision(BudgetRevision $revision, DownloadPdfRevisionAction $action): \Illuminate\Http\Response
    {
        return $action->execute($revision);
    }
}
