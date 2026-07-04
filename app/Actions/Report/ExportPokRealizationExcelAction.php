<?php

namespace App\Actions\Report;

use App\Models\BudgetRealization;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportPokRealizationExcelAction
{
    /**
     * Export POK Realizations to an Excel file.
     */
    public function execute(Request $request): StreamedResponse
    {
        $selectedYearId = $request->input('fiscal_year_id');
        $query = BudgetRealization::with([
            'activityBudget.activity.program',
            'activityBudget.subComponent.component.subOutput.output',
            'procurement.vendor',
            'items',
        ]);

        if ($selectedYearId) {
            $query->whereHas('activityBudget', function ($q) use ($selectedYearId) {
                $q->where('fiscal_year_id', $selectedYearId);
            });
        }

        $realizations = $query->orderBy('realization_date', 'asc')->get();

        return response()->streamDownload(function () use ($realizations) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'No',
                'Tanggal Realisasi',
                'Nomor Kuitansi',
                'Uraian Realisasi',
                'Kode MAK',
                'Akun Belanja',
                'Vendor/Penyedia',
                'Nomor SP/SPK',
                'Nomor BAST',
                'Nomor SPP',
                'Nomor SPM',
                'Nomor SP2D',
                'Item Barang/Jasa',
                'Vol',
                'Satuan',
                'Harga Satuan',
                'Subtotal (Bruto)',
                'PPN',
                'PPh 21',
                'PPh 22',
                'PPh 23',
                'Bersih (Netto)',
            ]));

            $no = 1;
            foreach ($realizations as $real) {
                $makCode = '';
                $act = $real->activityBudget->activity;
                if ($act) {
                    $makCode = ($act->program ? $act->program->code : '').'.'.$act->code;
                    $subc = $real->activityBudget->subComponent;
                    if ($subc) {
                        $comp = $subc->component;
                        $subo = $comp ? $comp->subOutput : null;
                        $out = $subo ? $subo->output : null;
                        if ($out) {
                            $makCode = $out->code.'.'.$subo->code.'.'.$comp->code.'.'.$subc->code;
                        }
                    }
                    $makCode .= '.'.$real->activityBudget->account_code;
                }

                foreach ($real->items as $item) {
                    $bruto = $item->volume * $item->unit_price;
                    $taxes = (float) $item->tax_ppn + (float) $item->tax_pph21 + (float) $item->tax_pph22 + (float) $item->tax_pph23;
                    $netto = $bruto - $taxes;

                    $writer->addRow(Row::fromValues([
                        $no++,
                        Carbon::parse($real->realization_date)->format('Y-m-d'),
                        $real->receipt_number ?? '',
                        $real->description ?? '',
                        $makCode,
                        $real->activityBudget->account_name,
                        $real->procurement && $real->procurement->vendor ? $real->procurement->vendor->name : ($real->vendor_name ?? ''),
                        $real->procurement ? $real->procurement->document_number : '',
                        $real->bast_number ?? '',
                        $real->spp_number ?? '',
                        $real->spm_number ?? '',
                        $real->sp2d_number ?? '',
                        $item->name,
                        $item->volume,
                        $item->unit,
                        $item->unit_price,
                        $bruto,
                        $item->tax_ppn,
                        $item->tax_pph21,
                        $item->tax_pph22,
                        $item->tax_pph23,
                        $netto,
                    ]));
                }
            }

            $writer->close();
        }, 'laporan-realisasi-pok.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
