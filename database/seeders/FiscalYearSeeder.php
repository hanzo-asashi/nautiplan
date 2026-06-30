<?php

namespace Database\Seeders;

use App\Models\FiscalYear;
use Illuminate\Database\Seeder;

class FiscalYearSeeder extends Seeder
{
    /**
     * Seed fiscal years 2024-2028, following calendar year (Jan-Dec).
     */
    public function run(): void
    {
        $years = [
            ['year' => 2024, 'is_active' => false, 'is_locked' => true],
            ['year' => 2025, 'is_active' => false, 'is_locked' => false],
            ['year' => 2026, 'is_active' => true, 'is_locked' => false],
            ['year' => 2027, 'is_active' => false, 'is_locked' => false],
            ['year' => 2028, 'is_active' => false, 'is_locked' => false],
        ];

        foreach ($years as $yearData) {
            FiscalYear::create([
                'year' => $yearData['year'],
                'start_date' => "{$yearData['year']}-01-01",
                'end_date' => "{$yearData['year']}-12-31",
                'is_active' => $yearData['is_active'],
                'is_locked' => $yearData['is_locked'],
            ]);
        }
    }
}
