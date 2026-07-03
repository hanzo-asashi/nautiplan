<?php

use App\Models\FiscalYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('rekap dipa output and komponen reports can be downloaded', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $fy = FiscalYear::create([
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_active' => true,
    ]);

    // Test output rekap download
    $outputResponse = $this->get(route('reports.export.rekap-output', ['fiscal_year_id' => $fy->id]));
    $outputResponse->assertSuccessful();
    $outputResponse->assertHeader('content-type', 'application/pdf');
    $outputResponse->assertHeader('content-disposition', 'inline; filename=laporan-rekap-output-2026.pdf');

    // Test komponen rekap download
    $komponenResponse = $this->get(route('reports.export.rekap-komponen', ['fiscal_year_id' => $fy->id]));
    $komponenResponse->assertSuccessful();
    $komponenResponse->assertHeader('content-type', 'application/pdf');
    $komponenResponse->assertHeader('content-disposition', 'inline; filename=laporan-rekap-komponen-2026.pdf');
});
