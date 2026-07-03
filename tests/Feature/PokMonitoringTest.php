<?php

use App\Models\FiscalYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pok monitoring page and excel export can be accessed', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $fy = FiscalYear::create([
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_active' => true,
    ]);

    // Check dashboard rendering
    $response = $this->get(route('reports.pok-monitoring'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('reports/PokMonitoring')
        ->has('tree')
        ->has('fiscalYears')
    );

    // Check excel export endpoint
    $excelResponse = $this->get(route('reports.export.pok-realization', ['fiscal_year_id' => $fy->id]));
    $excelResponse->assertOk();
    $excelResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $excelResponse->assertHeader('content-disposition', 'attachment; filename=laporan-realisasi-pok.xlsx');
});
