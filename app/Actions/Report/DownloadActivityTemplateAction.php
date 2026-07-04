<?php

namespace App\Actions\Report;

use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Unit;
use App\Models\User;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadActivityTemplateAction
{
    /**
     * Download the activity import template.
     */
    public function execute(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $headerStyle = clone (new Style)->withFontBold(true);

            $sheet1 = $writer->getCurrentSheet();
            $sheet1->setName('Template Data');

            $writer->addRow(Row::fromValuesWithStyle([
                'Kode Kegiatan (Wajib)',
                'Nama Kegiatan (Wajib)',
                'Deskripsi (Opsional)',
                'Kode Program (Wajib)',
                'Kode Unit Pelaksana (Wajib)',
                'Tahun Anggaran (Wajib)',
                'Email Penanggung Jawab (Opsional)',
                'Prioritas (low/medium/high) (Opsional, def: medium)',
                'Status (draft/approved/completed/cancelled) (Opsional, def: draft)',
                'Tanggal Mulai (YYYY-MM-DD) (Opsional)',
                'Tanggal Selesai (YYYY-MM-DD) (Opsional)',
                'Lokasi (Opsional)',
            ], $headerStyle));

            $writer->addRow(Row::fromValues([
                'KEG-001',
                'Pembangunan Infrastruktur Jaringan',
                'Membangun jaringan fiber optik di gedung utama',
                'PRG-01',
                'TIK',
                '2024',
                'admin@example.com',
                'high',
                'draft',
                '2024-02-01',
                '2024-06-30',
                'Gedung Utama',
            ]));

            $writer->addNewSheetAndMakeItCurrent();
            $sheet2 = $writer->getCurrentSheet();
            $sheet2->setName('Referensi Data');

            $writer->addRow(Row::fromValuesWithStyle(['--- DAFTAR PROGRAM ---'], $headerStyle));
            $writer->addRow(Row::fromValuesWithStyle(['Kode Program', 'Nama Program'], $headerStyle));
            foreach (Program::get(['code', 'name']) as $p) {
                $writer->addRow(Row::fromValues([$p->code, $p->name]));
            }
            $writer->addRow(Row::fromValues([' ']));

            $writer->addRow(Row::fromValuesWithStyle(['--- DAFTAR UNIT ---'], $headerStyle));
            $writer->addRow(Row::fromValuesWithStyle(['Kode Unit', 'Nama Unit'], $headerStyle));
            foreach (Unit::get(['code', 'name']) as $u) {
                $writer->addRow(Row::fromValues([$u->code, $u->name]));
            }
            $writer->addRow(Row::fromValues([' ']));

            $writer->addRow(Row::fromValuesWithStyle(['--- TAHUN ANGGARAN ---'], $headerStyle));
            $writer->addRow(Row::fromValuesWithStyle(['Tahun'], $headerStyle));
            foreach (FiscalYear::orderBy('year', 'desc')->get(['year']) as $fy) {
                $writer->addRow(Row::fromValues([$fy->year]));
            }
            $writer->addRow(Row::fromValues([' ']));

            $writer->addRow(Row::fromValuesWithStyle(['--- PENANGGUNG JAWAB (USER) ---'], $headerStyle));
            $writer->addRow(Row::fromValuesWithStyle(['Email', 'Nama'], $headerStyle));
            foreach (User::get(['email', 'name']) as $usr) {
                $writer->addRow(Row::fromValues([$usr->email, $usr->name]));
            }

            $writer->close();
        }, 'template-import-kegiatan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
