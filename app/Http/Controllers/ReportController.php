<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityBudget;
use App\Models\BudgetRealization;
use App\Models\BudgetRevision;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Unit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function gantt(Request $request): Response
    {
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year', 'is_active']);
        $activeYear = FiscalYear::where('is_active', true)->first() ?? FiscalYear::orderBy('year', 'desc')->first();

        $selectedYearId = $request->input('fiscal_year_id', $activeYear?->id);
        $selectedUnitId = $request->input('unit_id');

        $query = Activity::query()
            ->with(['unit', 'subActivities.assignedUser'])
            ->where('fiscal_year_id', $selectedYearId)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date');

        if ($selectedUnitId) {
            $query->where('unit_id', $selectedUnitId);
        }

        $activities = $query->get()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'code' => $activity->code,
                'name' => $activity->name,
                'status' => $activity->status,
                'start_date' => $activity->start_date ? $activity->start_date->format('Y-m-d') : null,
                'end_date' => $activity->end_date ? $activity->end_date->format('Y-m-d') : null,
                'progress_percentage' => $activity->progress_percentage,
                'unit_name' => $activity->unit?->name,
                'sub_activities' => $activity->subActivities->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->name,
                        'status' => $sub->status,
                        'start_date' => $sub->start_date ? $sub->start_date->format('Y-m-d') : null,
                        'end_date' => $sub->end_date ? $sub->end_date->format('Y-m-d') : null,
                        'progress_percentage' => $sub->progress_percentage,
                        'assigned_user_name' => $sub->assignedUser?->name,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $units = Unit::get(['id', 'name', 'code']);

        return Inertia::render('reports/Gantt', [
            'activities' => $activities,
            'fiscalYears' => $fiscalYears,
            'units' => $units,
            'filters' => [
                'fiscal_year_id' => $selectedYearId ? (int) $selectedYearId : null,
                'unit_id' => $selectedUnitId ? (int) $selectedUnitId : null,
            ],
        ]);
    }

    public function analytics(Request $request): Response
    {
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year', 'is_active']);
        $activeYear = FiscalYear::where('is_active', true)->first() ?? FiscalYear::orderBy('year', 'desc')->first();

        $selectedYearId = $request->input('fiscal_year_id', $activeYear?->id);

        // Budget vs Realization by Unit
        $unitsData = Unit::with([
            'activities' => function ($query) use ($selectedYearId) {
                $query->where('fiscal_year_id', $selectedYearId);
            },
            'activities.budgets.realizations',
        ])
            ->get()
            ->map(function ($unit) {
                $activities = $unit->activities;
                $pagu = $activities->flatMap->budgets->sum('amount');
                $realisasi = $activities->flatMap->budgets->flatMap->realizations->sum('amount');

                return [
                    'label' => $unit->name,
                    'value1' => (float) $pagu,
                    'value2' => (float) $realisasi,
                ];
            })
            ->filter(fn ($u) => $u['value1'] > 0 || $u['value2'] > 0)
            ->values()
            ->all();

        // Budget vs Realization by Program
        $programsData = Program::where('fiscal_year_id', $selectedYearId)
            ->with(['activities.budgets.realizations'])
            ->get()
            ->map(function ($program) {
                $pagu = $program->activities->flatMap->budgets->sum('amount');
                $realisasi = $program->activities->flatMap->budgets->flatMap->realizations->sum('amount');

                return [
                    'label' => $program->name,
                    'value1' => (float) $pagu,
                    'value2' => (float) $realisasi,
                ];
            })
            ->filter(fn ($p) => $p['value1'] > 0 || $p['value2'] > 0)
            ->values()
            ->all();

        // Multi-year comparison
        $multiYearData = FiscalYear::orderBy('year', 'asc')
            ->with(['activities.budgets.realizations', 'activities.indicators'])
            ->get()
            ->map(function ($fy) {
                $pagu = $fy->activities->flatMap->budgets->sum('amount');
                $realisasi = $fy->activities->flatMap->budgets->flatMap->realizations->sum('amount');
                $totalActivities = $fy->activities->count();

                $indicators = $fy->activities->flatMap->indicators;
                $achievementAvg = 0;
                if ($indicators->count() > 0) {
                    $totalPct = $indicators->sum(function ($ind) {
                        if (is_null($ind->actual_value) || $ind->target_value <= 0) {
                            return 0;
                        }

                        return min(100, round(($ind->actual_value / $ind->target_value) * 100));
                    });
                    $achievementAvg = round($totalPct / $indicators->count(), 1);
                }

                return [
                    'year' => $fy->year,
                    'total_activities' => $totalActivities,
                    'pagu' => (float) $pagu,
                    'realisasi' => (float) $realisasi,
                    'kpi_achievement' => $achievementAvg,
                ];
            })
            ->values()
            ->all();

        // 1. Monthly Absorption Trend
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
        ];

        $realizationsQuery = BudgetRealization::whereHas('activityBudget', function ($q) use ($selectedYearId) {
            $q->where('fiscal_year_id', $selectedYearId);
        })->get();

        $monthlyTrend = [];
        $runningSum = 0;
        foreach ($months as $mNum => $mName) {
            $mAmount = (float) $realizationsQuery->filter(function ($r) use ($mNum) {
                return Carbon::parse($r->realization_date)->month === $mNum;
            })->sum('amount');

            $runningSum += $mAmount;

            $monthlyTrend[] = [
                'month' => $mName,
                'amount' => $mAmount,
                'cumulative' => $runningSum,
            ];
        }

        // 2. Early Warning System (Pagu Kritis)
        $criticalBudgets = ActivityBudget::where('fiscal_year_id', $selectedYearId)
            ->with(['activity.unit', 'realizations'])
            ->get()
            ->map(function ($budget) {
                $realisasi = $budget->realizations->sum('amount');
                $remaining = $budget->amount - $realisasi;
                $pct = $budget->amount > 0 ? ($realisasi / $budget->amount) * 100 : 0;

                return [
                    'id' => $budget->id,
                    'account_code' => $budget->account_code,
                    'description' => $budget->description,
                    'unit' => $budget->activity->unit->name ?? '-',
                    'pagu' => (float) $budget->amount,
                    'realisasi' => (float) $realisasi,
                    'sisa' => (float) $remaining,
                    'percentage' => round($pct, 1),
                ];
            })
            ->filter(function ($b) {
                return ($b['percentage'] >= 85.0 && $b['sisa'] > 0) || ($b['sisa'] > 0 && $b['sisa'] <= 2000000.0);
            })
            ->sortBy('sisa')
            ->values()
            ->all();

        return Inertia::render('reports/Analytics', [
            'unitsData' => $unitsData,
            'programsData' => $programsData,
            'multiYearData' => $multiYearData,
            'monthlyTrend' => $monthlyTrend,
            'criticalBudgets' => $criticalBudgets,
            'fiscalYears' => $fiscalYears,
            'filters' => [
                'fiscal_year_id' => $selectedYearId ? (int) $selectedYearId : null,
            ],
            'importErrors' => session('importErrors'),
            'success' => session('success'),
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
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
                    $act->program->name,
                    $act->unit->name,
                    $act->fiscalYear->year,
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

    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'Kode Kegiatan',
                'Nama Kegiatan',
                'Deskripsi',
                'Kode Program',
                'Kode Unit',
                'Tahun Anggaran',
                'Email PJ Kegiatan',
                'Prioritas',
                'Status',
                'Tanggal Mulai (YYYY-MM-DD)',
                'Tanggal Selesai (YYYY-MM-DD)',
                'Lokasi',
            ]));

            // Pre-fetch some database codes to make sample helpful
            $sampleProgram = ($p = Program::first()) ? $p->code : 'PRG-001';
            $sampleUnit = ($u = Unit::first()) ? $u->code : 'UNT-UNT';
            $sampleYear = ($fy = FiscalYear::first()) ? $fy->year : 2026;
            $sampleEmail = ($usr = User::first()) ? $usr->email : 'responsible@pelayaran.ac.id';

            $writer->addRow(Row::fromValues([
                'ACT-SOS-001',
                'Sosialisasi Keselamatan Pelayaran',
                'Kegiatan sosialisasi mengenai prosedur keselamatan pelayaran terbaru.',
                $sampleProgram,
                $sampleUnit,
                $sampleYear,
                $sampleEmail,
                'MEDIUM',
                'DRAFT',
                '2026-03-01',
                '2026-03-10',
                'Aula Poltekpel Barombong',
            ]));

            $writer->close();
        }, 'template-import-kegiatan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,bin,ods',
        ]);

        $file = $request->file('file');

        $reader = new Reader;
        $reader->open($file->getRealPath());

        $rows = [];

        foreach ($reader->getSheetIterator() as $sheetIndex => $sheet) {
            if ($sheetIndex !== 1) {
                continue;
            }

            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $values = $row->toArray();

                if ($rowIndex === 1) {
                    continue;
                }

                if (empty(array_filter($values))) {
                    continue;
                }

                $rows[] = [
                    'row_number' => $rowIndex,
                    'data' => $values,
                ];
            }
        }
        $reader->close();

        if (empty($rows)) {
            return back()->withErrors(['file' => 'File Excel kosong atau tidak memiliki baris data.']);
        }

        $errors = [];
        $importedCount = 0;

        $programs = Program::get()->keyBy('code');
        $units = Unit::get()->keyBy('code');
        $fiscalYears = FiscalYear::get()->keyBy('year');
        $users = User::get()->keyBy('email');

        DB::beginTransaction();

        try {
            $getString = function ($val): string {
                if (is_scalar($val)) {
                    return (string) $val;
                }
                if ($val instanceof \DateTimeInterface) {
                    return $val->format('Y-m-d');
                }

                return '';
            };

            foreach ($rows as $rowInfo) {
                $rowNum = $rowInfo['row_number'];
                $data = $rowInfo['data'];

                $code = isset($data[0]) ? trim($getString($data[0])) : '';
                $name = isset($data[1]) ? trim($getString($data[1])) : '';
                $description = isset($data[2]) ? trim($getString($data[2])) : null;
                $programCode = isset($data[3]) ? trim($getString($data[3])) : '';
                $unitCode = isset($data[4]) ? trim($getString($data[4])) : '';
                $yearVal = isset($data[5]) ? trim($getString($data[5])) : '';
                $emailPj = isset($data[6]) ? trim($getString($data[6])) : null;
                $priority = isset($data[7]) ? strtolower(trim($getString($data[7]))) : 'medium';
                $status = isset($data[8]) ? strtolower(trim($getString($data[8]))) : 'draft';
                $startDateVal = $data[9] ?? null;
                $endDateVal = $data[10] ?? null;
                $location = isset($data[11]) ? trim($getString($data[11])) : null;

                $rowErrors = [];

                if (! $code) {
                    $rowErrors[] = 'Kode Kegiatan wajib diisi.';
                } elseif (Activity::where('code', $code)->exists()) {
                    $rowErrors[] = "Kode Kegiatan '{$code}' sudah terdaftar di sistem.";
                }

                if (! $name) {
                    $rowErrors[] = 'Nama Kegiatan wajib diisi.';
                }

                $program = $programCode ? ($programs->get($programCode) ?? Program::where('code', $programCode)->first()) : null;
                if (! $programCode) {
                    $rowErrors[] = 'Kode Program wajib diisi.';
                } elseif (! $program) {
                    $rowErrors[] = "Kode Program '{$programCode}' tidak ditemukan.";
                }

                $unit = $unitCode ? ($units->get($unitCode) ?? Unit::where('code', $unitCode)->first()) : null;
                if (! $unitCode) {
                    $rowErrors[] = 'Kode Unit wajib diisi.';
                } elseif (! $unit) {
                    $rowErrors[] = "Kode Unit '{$unitCode}' tidak ditemukan.";
                }

                $fiscalYear = $yearVal ? ($fiscalYears->get((int) $yearVal) ?? FiscalYear::where('year', (int) $yearVal)->first()) : null;
                if (! $yearVal) {
                    $rowErrors[] = 'Tahun Anggaran wajib diisi.';
                } elseif (! $fiscalYear) {
                    $rowErrors[] = "Tahun Anggaran '{$yearVal}' tidak ditemukan.";
                }

                $user = $emailPj ? ($users->get($emailPj) ?? User::where('email', $emailPj)->first()) : null;
                if ($emailPj && ! $user) {
                    $rowErrors[] = "Email Penanggung Jawab '{$emailPj}' tidak terdaftar.";
                }

                if (! in_array($priority, ['low', 'medium', 'high', 'critical'])) {
                    $rowErrors[] = "Prioritas '{$priority}' tidak valid (pilihan: low, medium, high, critical).";
                }

                if (! in_array($status, ['draft', 'proposed', 'approved', 'in_progress', 'completed', 'cancelled'])) {
                    $rowErrors[] = "Status '{$status}' tidak valid (pilihan: draft, proposed, approved, in_progress, completed, cancelled).";
                }

                $startDate = null;
                $endDate = null;

                if ($startDateVal) {
                    if ($startDateVal instanceof \DateTimeInterface) {
                        $startDate = Carbon::instance($startDateVal);
                    } else {
                        try {
                            $startDate = Carbon::parse($getString($startDateVal));
                        } catch (\Exception $e) {
                            $rowErrors[] = 'Format Tanggal Mulai tidak valid.';
                        }
                    }
                }

                if ($endDateVal) {
                    if ($endDateVal instanceof \DateTimeInterface) {
                        $endDate = Carbon::instance($endDateVal);
                    } else {
                        try {
                            $endDate = Carbon::parse($getString($endDateVal));
                        } catch (\Exception $e) {
                            $rowErrors[] = 'Format Tanggal Selesai tidak valid.';
                        }
                    }
                    if ($startDate && $endDate && $endDate->lt($startDate)) {
                        $rowErrors[] = 'Tanggal Selesai tidak boleh mendahului Tanggal Mulai.';
                    }
                }

                if (! empty($rowErrors)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'code' => $code ?: 'TANPA-KODE',
                        'messages' => $rowErrors,
                    ];

                    continue;
                }

                Activity::create([
                    'code' => $code,
                    'name' => $name,
                    'description' => $description,
                    'program_id' => $program->id,
                    'unit_id' => $unit->id,
                    'fiscal_year_id' => $fiscalYear->id,
                    'responsible_user_id' => $user ? $user->id : auth()->id(),
                    'priority' => $priority,
                    'status' => $status,
                    'start_date' => $startDate?->format('Y-m-d'),
                    'end_date' => $endDate?->format('Y-m-d'),
                    'location' => $location,
                    'progress_percentage' => 0,
                ]);

                $importedCount++;
            }

            if (! empty($errors)) {
                DB::rollBack();

                return back()->with('importErrors', $errors);
            }

            DB::commit();

            return back()->with('success', "Berhasil mengimpor {$importedCount} kegiatan.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['file' => 'Terjadi kesalahan sistem saat memproses file: '.$e->getMessage()]);
        }
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

        $terbilang = self::terbilang($realization->amount).' rupiah';
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

        $terbilang = self::terbilang($realization->amount).' rupiah';
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

        $terbilang = self::terbilang($realization->amount).' rupiah';
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

        $terbilang = self::terbilang($realization->amount).' rupiah';
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

        $terbilang = self::terbilang($realization->amount).' rupiah';
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

        $terbilang = self::terbilang($realization->amount).' rupiah';
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

        $terbilang = self::terbilang($realization->amount).' rupiah';
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

        $terbilang = self::terbilang($realization->amount).' rupiah';
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

        $terbilang = self::terbilang($taxAmount).' rupiah';
        $pdf = Pdf::loadView('pdf.ssp', compact('realization', 'taxType', 'taxAmount', 'terbilang'));

        return $pdf->stream("ssp-{$taxType}-".($realization->receipt_number ?? 'draft').'.pdf');
    }

    /**
     * Konversi angka nominal menjadi ejaan kalimat Terbilang Bahasa Indonesia
     */
    public static function terbilang(float $nilai): string
    {
        $nilai = abs($nilai);
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $temp = '';

        if ($nilai < 12) {
            $temp = ' '.$huruf[(int) $nilai];
        } elseif ($nilai < 20) {
            $temp = self::terbilang($nilai - 10).' belas';
        } elseif ($nilai < 100) {
            $temp = self::terbilang(floor($nilai / 10)).' puluh '.self::terbilang($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = ' seratus '.self::terbilang($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = self::terbilang(floor($nilai / 100)).' ratus '.self::terbilang($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = ' seribu '.self::terbilang($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = self::terbilang(floor($nilai / 1000)).' ribu '.self::terbilang($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = self::terbilang(floor($nilai / 1000000)).' juta '.self::terbilang($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $temp = self::terbilang(floor($nilai / 1000000000)).' miliar '.self::terbilang(fmod($nilai, 1000000000));
        } elseif ($nilai < 1000000000000000) {
            $temp = self::terbilang(floor($nilai / 1000000000000)).' triliun '.self::terbilang(fmod($nilai, 1000000000000));
        }

        return trim($temp);
    }

    public function calendar(Request $request): Response
    {
        $selectedYearId = $request->input('fiscal_year_id') ?: FiscalYear::where('is_active', true)->value('id') ?: FiscalYear::value('id');
        $selectedUnitId = $request->input('unit_id');
        $selectedStatus = $request->input('status');

        $query = Activity::with(['unit', 'subActivities.assignedUser'])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date');

        if ($selectedYearId) {
            $query->where('fiscal_year_id', $selectedYearId);
        }
        if ($selectedUnitId) {
            $query->where('unit_id', $selectedUnitId);
        }
        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        $activities = $query->get()->map(fn ($act) => [
            'id' => $act->id,
            'code' => $act->code,
            'name' => $act->name,
            'status' => $act->status,
            'priority' => $act->priority,
            'start_date' => $act->start_date ? $act->start_date->format('Y-m-d') : null,
            'end_date' => $act->end_date ? $act->end_date->format('Y-m-d') : null,
            'unit_name' => $act->unit->name,
            'progress_percentage' => $act->progress_percentage,
            'sub_activities' => $act->subActivities->map(fn ($sub) => [
                'id' => $sub->id,
                'name' => $sub->name,
                'status' => $sub->status,
                'start_date' => $sub->start_date ? $sub->start_date->format('Y-m-d') : null,
                'end_date' => $sub->end_date ? $sub->end_date->format('Y-m-d') : null,
                'progress_percentage' => $sub->progress_percentage,
                'assigned_user_name' => $sub->assignedUser?->name,
            ])->all(),
        ]);

        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year', 'is_active']);
        $units = Unit::orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('reports/Calendar', [
            'activities' => $activities,
            'fiscalYears' => $fiscalYears,
            'units' => $units,
            'filters' => [
                'fiscal_year_id' => (int) $selectedYearId,
                'unit_id' => $selectedUnitId ? (int) $selectedUnitId : null,
                'status' => $selectedStatus,
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildPokTree(?int $selectedYearId): array
    {
        if (! $selectedYearId) {
            return [];
        }

        // Fetch Program tree
        $programs = Program::where('fiscal_year_id', $selectedYearId)
            ->with([
                'activities.outputs.subOutputs.components.subComponents.activityBudgets.realizations',
            ])
            ->get();

        $tree = [];
        foreach ($programs as $prog) {
            $activities = [];
            foreach ($prog->activities as $act) {
                $outputs = [];
                foreach ($act->outputs as $out) {
                    $subOutputs = [];
                    foreach ($out->subOutputs as $subOut) {
                        $components = [];
                        foreach ($subOut->components as $comp) {
                            $subComponents = [];
                            foreach ($comp->subComponents as $subComp) {
                                $budgets = [];
                                foreach ($subComp->activityBudgets as $budget) {
                                    $pagu = (float) $budget->amount;
                                    $realisasi = (float) $budget->realizations->sum('amount');
                                    $budgets[] = [
                                        'id' => $budget->id,
                                        'type' => 'budget',
                                        'code' => $budget->account_code,
                                        'name' => $budget->account_name,
                                        'pagu' => $pagu,
                                        'realisasi' => $realisasi,
                                        'sisa' => $pagu - $realisasi,
                                        'children' => [],
                                    ];
                                }

                                $pagu = (float) array_sum(array_column($budgets, 'pagu'));
                                $realisasi = (float) array_sum(array_column($budgets, 'realisasi'));

                                $subComponents[] = [
                                    'id' => $subComp->id,
                                    'type' => 'sub_component',
                                    'code' => $subComp->code,
                                    'name' => $subComp->name,
                                    'pagu' => $pagu,
                                    'realisasi' => $realisasi,
                                    'sisa' => $pagu - $realisasi,
                                    'children' => $budgets,
                                ];
                            }

                            $pagu = (float) array_sum(array_column($subComponents, 'pagu'));
                            $realisasi = (float) array_sum(array_column($subComponents, 'realisasi'));

                            $components[] = [
                                'id' => $comp->id,
                                'type' => 'component',
                                'code' => $comp->code,
                                'name' => $comp->name,
                                'pagu' => $pagu,
                                'realisasi' => $realisasi,
                                'sisa' => $pagu - $realisasi,
                                'children' => $subComponents,
                            ];
                        }

                        $pagu = (float) array_sum(array_column($components, 'pagu'));
                        $realisasi = (float) array_sum(array_column($components, 'realisasi'));

                        $subOutputs[] = [
                            'id' => $subOut->id,
                            'type' => 'sub_output',
                            'code' => $subOut->code,
                            'name' => $subOut->name,
                            'pagu' => $pagu,
                            'realisasi' => $realisasi,
                            'sisa' => $pagu - $realisasi,
                            'children' => $components,
                        ];
                    }

                    $pagu = (float) array_sum(array_column($subOutputs, 'pagu'));
                    $realisasi = (float) array_sum(array_column($subOutputs, 'realisasi'));

                    $outputs[] = [
                        'id' => $out->id,
                        'type' => 'output',
                        'code' => $out->code,
                        'name' => $out->name,
                        'pagu' => $pagu,
                        'realisasi' => $realisasi,
                        'sisa' => $pagu - $realisasi,
                        'children' => $subOutputs,
                    ];
                }

                $pagu = (float) array_sum(array_column($outputs, 'pagu'));
                $realisasi = (float) array_sum(array_column($outputs, 'realisasi'));

                $activities[] = [
                    'id' => $act->id,
                    'type' => 'activity',
                    'code' => $act->code,
                    'name' => $act->name,
                    'pagu' => $pagu,
                    'realisasi' => $realisasi,
                    'sisa' => $pagu - $realisasi,
                    'children' => $outputs,
                ];
            }

            $pagu = (float) array_sum(array_column($activities, 'pagu'));
            $realisasi = (float) array_sum(array_column($activities, 'realisasi'));

            $tree[] = [
                'id' => $prog->id,
                'type' => 'program',
                'code' => $prog->code,
                'name' => $prog->name,
                'pagu' => $pagu,
                'realisasi' => $realisasi,
                'sisa' => $pagu - $realisasi,
                'children' => $activities,
            ];
        }

        return $tree;
    }

    public function pokMonitoring(Request $request): Response
    {
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year', 'is_active']);
        $activeYear = FiscalYear::where('is_active', true)->first() ?? FiscalYear::orderBy('year', 'desc')->first();

        $selectedYearId = $request->input('fiscal_year_id', $activeYear?->id);

        $tree = $this->buildPokTree($selectedYearId ? (int) $selectedYearId : null);

        return Inertia::render('reports/PokMonitoring', [
            'tree' => $tree,
            'fiscalYears' => $fiscalYears,
            'filters' => [
                'fiscal_year_id' => $selectedYearId ? (int) $selectedYearId : null,
            ],
        ]);
    }

    public function exportPokRealizationExcel(Request $request): StreamedResponse
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
                        $real->realization_date->format('Y-m-d'),
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

    public function downloadPdfRekapOutput(Request $request): \Illuminate\Http\Response
    {
        $activeYear = FiscalYear::where('is_active', true)->first() ?? FiscalYear::orderBy('year', 'desc')->first();
        $selectedYearId = $request->input('fiscal_year_id', $activeYear?->id);
        $fiscalYear = FiscalYear::where('id', $selectedYearId)->first();

        $tree = $this->buildPokTree($selectedYearId ? (int) $selectedYearId : null);

        $totalPagu = (float) array_sum(array_column($tree, 'pagu'));
        $totalRealisasi = (float) array_sum(array_column($tree, 'realisasi'));
        $totalSisa = $totalPagu - $totalRealisasi;

        $pdf = Pdf::loadView('pdf.rekap-output', compact('tree', 'fiscalYear', 'totalPagu', 'totalRealisasi', 'totalSisa'))
            ->setPaper('a4', 'landscape');

        $yearName = $fiscalYear !== null ? (string) $fiscalYear->year : '';

        return $pdf->stream('laporan-rekap-output-'.$yearName.'.pdf');
    }

    public function downloadPdfRekapKomponen(Request $request): \Illuminate\Http\Response
    {
        $activeYear = FiscalYear::where('is_active', true)->first() ?? FiscalYear::orderBy('year', 'desc')->first();
        $selectedYearId = $request->input('fiscal_year_id', $activeYear?->id);
        $fiscalYear = FiscalYear::where('id', $selectedYearId)->first();

        $tree = $this->buildPokTree($selectedYearId ? (int) $selectedYearId : null);

        $totalPagu = (float) array_sum(array_column($tree, 'pagu'));
        $totalRealisasi = (float) array_sum(array_column($tree, 'realisasi'));
        $totalSisa = $totalPagu - $totalRealisasi;

        $pdf = Pdf::loadView('pdf.rekap-komponen', compact('tree', 'fiscalYear', 'totalPagu', 'totalRealisasi', 'totalSisa'))
            ->setPaper('a4', 'landscape');

        $yearName = $fiscalYear !== null ? (string) $fiscalYear->year : '';

        return $pdf->stream('laporan-rekap-komponen-'.$yearName.'.pdf');
    }

    public function downloadPdfRevision(BudgetRevision $revision): \Illuminate\Http\Response
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
