<?php

namespace App\Actions\Report;

use App\Models\Activity;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportActivityExcelAction
{
    /**
     * Export activities to an Excel file.
     */
    public function execute(Request $request): StreamedResponse
    {
        $selectedYearId = $request->input('fiscal_year_id');
        $query = Activity::with(['program', 'unit', 'fiscalYear', 'responsibleUser', 'budgets']);

        if ($selectedYearId) {
            $query->where('fiscal_year_id', $selectedYearId);
        }

        $activities = $query->get();

        return response()->streamDownload(function () use ($activities) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'Kode Kegiatan',
                'Nama Kegiatan',
                'Program',
                'Unit Pelaksana',
                'Tahun Anggaran',
                'Penanggung Jawab',
                'Prioritas',
                'Status',
                'Mulai',
                'Selesai',
                'Lokasi',
                'Progres (%)',
                'Pagu Anggaran (IDR)',
            ]));

            foreach ($activities as $act) {
                $writer->addRow(Row::fromValues([
                    $act->code,
                    $act->name,
                    $act->program->name ?? '',
                    $act->unit->name ?? '',
                    $act->fiscalYear->year ?? '',
                    $act->responsibleUser ? $act->responsibleUser->name : '',
                    strtoupper($act->priority),
                    strtoupper($act->status),
                    $act->start_date ? $act->start_date->format('Y-m-d') : '',
                    $act->end_date ? $act->end_date->format('Y-m-d') : '',
                    $act->location ?? '',
                    $act->progress_percentage,
                    $act->budgets->sum('amount'),
                ]));
            }

            $writer->close();
        }, 'laporan-rencana-kegiatan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
