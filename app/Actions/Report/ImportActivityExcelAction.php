<?php

namespace App\Actions\Report;

use App\Models\Activity;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader;

class ImportActivityExcelAction
{
    /**
     * Import activities from an Excel file.
     *
     * @return array{errors?: array<int, mixed>, success?: string, fileError?: string}
     */
    public function execute(Request $request): array
    {
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
            return ['fileError' => 'File Excel kosong atau tidak memiliki baris data.'];
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

                return ['errors' => $errors];
            }

            DB::commit();

            return ['success' => "Berhasil mengimpor {$importedCount} kegiatan."];

        } catch (\Exception $e) {
            DB::rollBack();

            return ['fileError' => 'Terjadi kesalahan sistem saat memproses file: '.$e->getMessage()];
        }
    }
}
