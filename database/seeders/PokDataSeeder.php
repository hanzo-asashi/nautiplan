<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityBudget;
use App\Models\BudgetItem;
use App\Models\Component;
use App\Models\FiscalYear;
use App\Models\Output;
use App\Models\Program;
use App\Models\SubComponent;
use App\Models\SubOutput;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use OpenSpout\Reader\XLSX\Reader;

class PokDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('docs/MATRIKS REVSI - POK BLU 14 Mei 2025 (Saldo Awal).xlsx');

        if (! file_exists($filePath)) {
            $this->command->warn("POK Excel file not found at: {$filePath}");

            return;
        }

        $reader = new Reader;
        $reader->open($filePath);

        $currentProgramId = null;
        $currentActivityId = null;
        $currentOutputId = null;
        $currentSubOutputId = null;
        $currentComponentId = null;
        $currentSubComponentId = null;
        $currentActivityBudgetId = null;

        $fy = FiscalYear::where('is_active', true)->first() ?? FiscalYear::first();
        if (! $fy) {
            $fy = FiscalYear::create([
                'year' => 2026,
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_active' => true,
            ]);
        }

        // Get default unit for dynamic creation
        $defaultUnit = Unit::first() ?? Unit::create([
            'code' => 'DIR',
            'name' => 'Direktorat',
            'is_active' => true,
        ]);

        foreach ($reader->getSheetIterator() as $sheet) {
            if ($sheet->getName() !== 'BLU REV 5') {
                continue;
            }

            $this->command->info("Parsing sheet 'BLU REV 5' and building DIPA/POK tree...");

            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                // Skip headers
                if ($rowIndex < 9) {
                    continue;
                }

                $values = $row->toArray();
                $code = isset($values[1]) ? $this->getStringValue($values[1]) : '';
                $uraian = isset($values[2]) ? $this->getStringValue($values[2]) : '';
                $volume = isset($values[3]) ? $values[3] : null;
                $unit = isset($values[4]) ? $this->getStringValue($values[4]) : '';
                $unitPrice = isset($values[5]) ? $values[5] : null;
                $total = isset($values[6]) ? $values[6] : null;

                if (empty($code) && empty($uraian)) {
                    continue;
                }

                // 1. Check Program (pattern: 022.12.DL or starts with 022)
                if (preg_match('/^022\./', $code)) {
                    $programCode = ($code === '022.12.DL') ? 'DL.1975' : (($code === '022.12.WA') ? 'WA.3996' : $code);
                    $program = Program::where('code', $programCode)->first();
                    if (! $program) {
                        $program = Program::create([
                            'code' => $programCode,
                            'name' => $uraian,
                            'unit_id' => $defaultUnit->id,
                            'fiscal_year_id' => $fy->id,
                            'status' => 'active',
                            'priority' => 'high',
                            'total_budget' => is_numeric($total) ? (float) $total : 0,
                            'created_by' => 1,
                        ]);
                    }
                    $currentProgramId = $program->id;

                    // Reset children state
                    $currentActivityId = null;
                    $currentOutputId = null;
                    $currentSubOutputId = null;
                    $currentComponentId = null;
                    $currentSubComponentId = null;
                    $currentActivityBudgetId = null;

                    continue;
                }

                // Ensure program exists before children
                if (! $currentProgramId) {
                    $program = Program::firstOrCreate([
                        'code' => '022.12.DL',
                    ], [
                        'name' => 'Program Pendidikan dan Pelatihan Vokasi',
                        'unit_id' => $defaultUnit->id,
                        'fiscal_year_id' => $fy->id,
                        'status' => 'active',
                        'priority' => 'high',
                        'total_budget' => 0,
                        'created_by' => 1,
                    ]);
                    $currentProgramId = $program->id;
                }

                // 2. Check Activity (pattern: 4 digits, e.g. 1975)
                if (preg_match('/^\d{4}$/', $code)) {
                    $activityCode = $code;
                    $activity = Activity::where('code', 'like', "%{$activityCode}%")->first();
                    if (! $activity) {
                        $activity = Activity::create([
                            'code' => $activityCode,
                            'name' => $uraian,
                            'program_id' => $currentProgramId,
                            'unit_id' => $defaultUnit->id,
                            'fiscal_year_id' => $fy->id,
                            'status' => 'approved',
                            'priority' => 'high',
                            'start_date' => '2026-01-01',
                            'end_date' => '2026-12-31',
                            'progress_percentage' => 0,
                        ]);
                    }
                    $currentActivityId = $activity->id;

                    // Reset children state
                    $currentOutputId = null;
                    $currentSubOutputId = null;
                    $currentComponentId = null;
                    $currentSubComponentId = null;
                    $currentActivityBudgetId = null;

                    continue;
                }

                // Ensure activity exists before children
                if (! $currentActivityId) {
                    $activity = Activity::firstOrCreate([
                        'code' => '1975',
                    ], [
                        'name' => 'Pengembangan SDM Transportasi',
                        'program_id' => $currentProgramId,
                        'unit_id' => $defaultUnit->id,
                        'fiscal_year_id' => $fy->id,
                        'status' => 'approved',
                        'priority' => 'high',
                        'start_date' => '2026-01-01',
                        'end_date' => '2026-12-31',
                        'progress_percentage' => 0,
                    ]);
                    $currentActivityId = $activity->id;
                }

                // 3. Check Output (pattern: e.g. 1975.DCB)
                if (preg_match('/^\d{4}\.[A-Z]{3}$/', $code)) {
                    $output = Output::firstOrCreate([
                        'activity_id' => $currentActivityId,
                        'code' => $code,
                    ], [
                        'name' => $uraian,
                    ]);
                    $currentOutputId = $output->id;

                    // Reset children state
                    $currentSubOutputId = null;
                    $currentComponentId = null;
                    $currentSubComponentId = null;
                    $currentActivityBudgetId = null;

                    continue;
                }

                // Ensure output exists
                if (! $currentOutputId) {
                    $output = Output::firstOrCreate([
                        'activity_id' => $currentActivityId,
                        'code' => '1975.DCB',
                    ], [
                        'name' => 'Pendidikan Bidang Infrastruktur',
                    ]);
                    $currentOutputId = $output->id;
                }

                // 4. Check Sub Output (pattern: e.g. 1975.DCB.008)
                if (preg_match('/^\d{4}\.[A-Z]{3}\.\d{3}$/', $code)) {
                    $subOutput = SubOutput::firstOrCreate([
                        'output_id' => $currentOutputId,
                        'code' => $code,
                    ], [
                        'name' => $uraian,
                    ]);
                    $currentSubOutputId = $subOutput->id;

                    // Reset children state
                    $currentComponentId = null;
                    $currentSubComponentId = null;
                    $currentActivityBudgetId = null;

                    continue;
                }

                // Ensure sub output exists
                if (! $currentSubOutputId) {
                    $subOutput = SubOutput::firstOrCreate([
                        'output_id' => $currentOutputId,
                        'code' => '1975.DCB.008',
                    ], [
                        'name' => 'Diklat Teknis Pengembangan ASN Transportasi Laut',
                    ]);
                    $currentSubOutputId = $subOutput->id;
                }

                // 5. Check Component (pattern: 3 digits, e.g. 501)
                if (preg_match('/^\d{3}$/', $code)) {
                    $component = Component::firstOrCreate([
                        'sub_output_id' => $currentSubOutputId,
                        'code' => $code,
                    ], [
                        'name' => $uraian,
                    ]);
                    $currentComponentId = $component->id;

                    // Reset children state
                    $currentSubComponentId = null;
                    $currentActivityBudgetId = null;

                    continue;
                }

                // 6. Check Sub Component (pattern: e.g. A, B, C)
                if (preg_match('/^[A-Z]$/', $code)) {
                    // Ensure component exists (fallback if component was skipped in Excel)
                    if (! $currentComponentId) {
                        $component = Component::firstOrCreate([
                            'sub_output_id' => $currentSubOutputId,
                            'code' => '000',
                        ], [
                            'name' => 'Default Component',
                        ]);
                        $currentComponentId = $component->id;
                    }

                    $subComponent = SubComponent::firstOrCreate([
                        'component_id' => $currentComponentId,
                        'code' => $code,
                    ], [
                        'name' => $uraian,
                    ]);
                    $currentSubComponentId = $subComponent->id;

                    // Reset children state
                    $currentActivityBudgetId = null;

                    continue;
                }

                // 7. Check Account (pattern: 6 digits, e.g. 525119)
                if (preg_match('/^\d{6}$/', $code)) {
                    // Ensure sub component exists (fallback if sub component was skipped in Excel)
                    if (! $currentSubComponentId) {
                        if (! $currentComponentId) {
                            $component = Component::firstOrCreate([
                                'sub_output_id' => $currentSubOutputId,
                                'code' => '000',
                            ], [
                                'name' => 'Default Component',
                            ]);
                            $currentComponentId = $component->id;
                        }

                        $subComponent = SubComponent::firstOrCreate([
                            'component_id' => $currentComponentId,
                            'code' => '000',
                        ], [
                            'name' => 'Default Sub Component',
                        ]);
                        $currentSubComponentId = $subComponent->id;
                    }

                    $category = 'other';
                    if (str_starts_with($code, '51') || $code === '525111') {
                        $category = 'personnel';
                    } elseif (str_starts_with($code, '52')) {
                        $category = 'goods_services';
                    } elseif (str_starts_with($code, '53')) {
                        $category = 'capital';
                    }

                    $activityBudget = ActivityBudget::firstOrCreate([
                        'activity_id' => $currentActivityId,
                        'account_code' => $code,
                        'fiscal_year_id' => $fy->id,
                    ], [
                        'sub_component_id' => $currentSubComponentId,
                        'budget_category' => $category,
                        'account_name' => $uraian,
                        'description' => $uraian,
                        'amount' => is_numeric($total) ? (float) $total : 0,
                        'version' => 1,
                    ]);

                    if (empty($activityBudget->sub_component_id) && $currentSubComponentId) {
                        $activityBudget->update(['sub_component_id' => $currentSubComponentId]);
                    }

                    $currentActivityBudgetId = $activityBudget->id;

                    continue;
                }

                // 8. Check Budget Item (code is empty, and Uraian starts with a hyphen '-')
                if (empty($code) && preg_match('/^\s*-\s*(.+)/', $uraian, $matches)) {
                    // Ensure account exists
                    if (! $currentActivityBudgetId) {
                        continue;
                    }

                    $itemName = trim($matches[1]);
                    $parsedVolume = is_numeric($volume) ? (float) $volume : 1;
                    $parsedUnitPrice = is_numeric($unitPrice) ? (float) $unitPrice : 0;
                    $parsedTotal = is_numeric($total) ? (float) $total : ($parsedVolume * $parsedUnitPrice);

                    BudgetItem::create([
                        'activity_budget_id' => $currentActivityBudgetId,
                        'name' => $itemName,
                        'volume' => $parsedVolume,
                        'unit' => $unit ?: 'Pcs',
                        'unit_price' => $parsedUnitPrice,
                        'total' => $parsedTotal,
                    ]);
                }
            }
        }

        $reader->close();
        $this->command->info('POK seeding successfully completed!');
    }

    /**
     * Get string value from a mixed cell.
     */
    private function getStringValue(mixed $value): string
    {
        if (is_string($value) || is_numeric($value)) {
            return trim((string) $value);
        }

        return '';
    }
}
