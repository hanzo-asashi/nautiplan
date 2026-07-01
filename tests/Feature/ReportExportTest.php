<?php

use App\Models\Activity;
use App\Models\ActivityReport;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->fiscalYear = FiscalYear::create([
        'year' => 2026,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_active' => true,
    ]);

    $this->unit = Unit::create([
        'code' => 'UNT-TEST',
        'name' => 'Unit Test',
        'is_active' => true,
    ]);

    $this->program = Program::create([
        'code' => 'PRG-TEST',
        'name' => 'Program Test',
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'created_by' => $this->user->id,
    ]);

    $this->activity = Activity::create([
        'code' => 'ACT-TEST',
        'name' => 'Activity Test',
        'program_id' => $this->program->id,
        'unit_id' => $this->unit->id,
        'fiscal_year_id' => $this->fiscalYear->id,
        'status' => 'draft',
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-10',
        'progress_percentage' => 0,
        'responsible_user_id' => $this->user->id,
    ]);
});

test('guest cannot access report endpoints', function () {
    $this->get(route('reports.gantt'))->assertRedirect(route('login'));
    $this->get(route('reports.analytics'))->assertRedirect(route('login'));
    $this->get(route('reports.export.excel'))->assertRedirect(route('login'));
    $this->get(route('reports.import.template'))->assertRedirect(route('login'));
    $this->post(route('reports.import.excel'))->assertRedirect(route('login'));
    $this->get(route('reports.activity.pdf', $this->activity))->assertRedirect(route('login'));
    $this->get(route('reports.quarterly.pdf', [$this->activity, 'Q1']))->assertRedirect(route('login'));
});

test('user can view gantt timeline page', function () {
    $response = $this->actingAs($this->user)->get(route('reports.gantt'));
    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('reports/Gantt')->has('activities')->has('units')->has('fiscalYears'));
});

test('user can view analytics and reports page', function () {
    $response = $this->actingAs($this->user)->get(route('reports.analytics'));
    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('reports/Analytics')->has('unitsData')->has('programsData')->has('multiYearData'));
});

test('user can export activities list to excel', function () {
    $response = $this->actingAs($this->user)->get(route('reports.export.excel'));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->assertHeader('content-disposition', 'attachment; filename=laporan-rencana-kegiatan.xlsx');
});

test('user can download excel template', function () {
    $response = $this->actingAs($this->user)->get(route('reports.import.template'));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->assertHeader('content-disposition', 'attachment; filename=template-import-kegiatan.xlsx');
});

test('user can download activity detail pdf', function () {
    $response = $this->actingAs($this->user)->get(route('reports.activity.pdf', $this->activity));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertHeader('content-disposition', 'attachment; filename=detail-kegiatan-ACT-TEST.pdf');
});

test('user can download quarterly report pdf', function () {
    $report = ActivityReport::create([
        'activity_id' => $this->activity->id,
        'quarter' => 'Q1',
        'status' => 'submitted',
        'progress_description' => 'Progress deskripsi',
        'obstacles' => 'Hambatan',
        'solutions' => 'Solusi',
        'submitted_by' => $this->user->id,
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($this->user)->get(route('reports.quarterly.pdf', [$this->activity, 'Q1']));
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
    $response->assertHeader('content-disposition', 'attachment; filename=laporan-monev-ACT-TEST-Q1.pdf');
});

test('user can import activities from excel', function () {
    // Generate a temporary excel file using OpenSpout
    $tempFile = tempnam(sys_get_temp_dir(), 'import_').'.xlsx';

    $writer = new Writer;
    $writer->openToFile($tempFile);

    // Header row
    $writer->addRow(Row::fromValues([
        'Kode Kegiatan', 'Nama Kegiatan', 'Deskripsi', 'Kode Program',
        'Kode Unit', 'Tahun Anggaran', 'Email PJ Kegiatan', 'Prioritas',
        'Status', 'Tanggal Mulai', 'Tanggal Selesai', 'Lokasi',
    ]));

    // Data row (Valid)
    $writer->addRow(Row::fromValues([
        'ACT-NEW-99', 'Kegiatan Impor Baru', 'Deskripsi impor', 'PRG-TEST',
        'UNT-TEST', 2026, $this->user->email, 'HIGH', 'DRAFT',
        '2026-05-01', '2026-05-15', 'Lab Komputer',
    ]));

    $writer->close();

    $uploadedFile = new UploadedFile(
        $tempFile,
        'import.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($this->user)->post(route('reports.import.excel'), [
        'file' => $uploadedFile,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('activities', [
        'code' => 'ACT-NEW-99',
        'name' => 'Kegiatan Impor Baru',
        'priority' => 'high',
        'status' => 'draft',
    ]);

    @unlink($tempFile);
});

test('excel import validates invalid program and unit codes', function () {
    $tempFile = tempnam(sys_get_temp_dir(), 'import_').'.xlsx';

    $writer = new Writer;
    $writer->openToFile($tempFile);

    $writer->addRow(Row::fromValues([
        'Kode Kegiatan', 'Nama Kegiatan', 'Deskripsi', 'Kode Program',
        'Kode Unit', 'Tahun Anggaran', 'Email PJ Kegiatan', 'Prioritas',
        'Status', 'Tanggal Mulai', 'Tanggal Selesai', 'Lokasi',
    ]));

    // Invalid program code ('PRG-INVALID') and invalid unit code ('UNT-INVALID')
    $writer->addRow(Row::fromValues([
        'ACT-ERR-99', 'Kegiatan Gagal', 'Deskripsi', 'PRG-INVALID',
        'UNT-INVALID', 2026, $this->user->email, 'HIGH', 'DRAFT',
        '2026-05-01', '2026-05-15', 'Lab',
    ]));

    $writer->close();

    $uploadedFile = new UploadedFile(
        $tempFile,
        'import.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );

    $response = $this->actingAs($this->user)->post(route('reports.import.excel'), [
        'file' => $uploadedFile,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('importErrors');

    $importErrors = session('importErrors');
    expect($importErrors)->toHaveCount(1);
    expect($importErrors[0]['messages'])->toContain("Kode Program 'PRG-INVALID' tidak ditemukan.");
    expect($importErrors[0]['messages'])->toContain("Kode Unit 'UNT-INVALID' tidak ditemukan.");

    $this->assertDatabaseMissing('activities', [
        'code' => 'ACT-ERR-99',
    ]);

    @unlink($tempFile);
});
