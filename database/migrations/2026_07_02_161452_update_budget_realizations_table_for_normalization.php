<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budget_realizations', function (Blueprint $table) {
            $table->foreignId('procurement_id')->nullable()->constrained('procurements')->onDelete('set null');
            $table->string('bast_number', 100)->nullable();
            $table->date('bast_date')->nullable();
            $table->string('bap_number', 100)->nullable();
            $table->date('bap_date')->nullable();
            $table->string('ba_penyerahan_number', 100)->nullable();
            $table->date('ba_penyerahan_date')->nullable();
        });

        // Migrate existing flat data
        $realizations = DB::table('budget_realizations')->get();
        foreach ($realizations as $real) {
            // Check if there is existing vendor/procurement data in this row
            $hasVendor = isset($real->vendor_name) && ! empty($real->vendor_name);
            $hasProc = isset($real->procurement_number) && ! empty($real->procurement_number);

            if ($hasVendor || $hasProc) {
                // 1. Create or Find Vendor
                $vendorName = $hasVendor ? $real->vendor_name : 'Penyedia Jasa/Barang';
                $vendorId = DB::table('vendors')->where('name', $vendorName)->value('id');

                if (! $vendorId) {
                    $vendorId = DB::table('vendors')->insertGetId([
                        'name' => $vendorName,
                        'npwp' => $real->vendor_npwp ?? null,
                        'address' => $real->vendor_address ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // 2. Create Procurement
                $procurementId = DB::table('procurements')->insertGetId([
                    'activity_budget_id' => $real->activity_budget_id,
                    'vendor_id' => $vendorId,
                    'title' => $real->description ?: 'Paket Pengadaan',
                    'procurement_type' => 'surat_pesanan',
                    'document_number' => $real->procurement_number ?: 'SP-'.uniqid(),
                    'document_date' => $real->procurement_date ?: $real->realization_date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 3. Link Realization
                DB::table('budget_realizations')
                    ->where('id', $real->id)
                    ->update([
                        'procurement_id' => $procurementId,
                        'ba_penyerahan_number' => $real->procurement_number ?? null,
                        'ba_penyerahan_date' => $real->procurement_date ?? null,
                    ]);
            }
        }

        // Drop old columns
        Schema::table('budget_realizations', function (Blueprint $table) {
            $table->dropColumn(['vendor_name', 'vendor_address', 'vendor_npwp', 'procurement_number', 'procurement_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add old columns
        Schema::table('budget_realizations', function (Blueprint $table) {
            $table->string('vendor_name')->nullable();
            $table->text('vendor_address')->nullable();
            $table->string('vendor_npwp', 50)->nullable();
            $table->string('procurement_number', 100)->nullable();
            $table->date('procurement_date')->nullable();
        });

        // Restore data from procurements/vendors tables if possible
        $realizations = DB::table('budget_realizations')->whereNotNull('procurement_id')->get();
        foreach ($realizations as $real) {
            $proc = DB::table('procurements')->where('id', $real->procurement_id)->first();
            if ($proc) {
                $vendor = DB::table('vendors')->where('id', $proc->vendor_id)->first();
                DB::table('budget_realizations')
                    ->where('id', $real->id)
                    ->update([
                        'vendor_name' => $vendor ? $vendor->name : null,
                        'vendor_address' => $vendor ? $vendor->address : null,
                        'vendor_npwp' => $vendor ? $vendor->npwp : null,
                        'procurement_number' => $proc->document_number,
                        'procurement_date' => $proc->document_date,
                    ]);
            }
        }

        // Drop new columns
        Schema::table('budget_realizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('procurement_id');
            $table->dropColumn(['bast_number', 'bast_date', 'bap_number', 'bap_date', 'ba_penyerahan_number', 'ba_penyerahan_date']);
        });
    }
};
