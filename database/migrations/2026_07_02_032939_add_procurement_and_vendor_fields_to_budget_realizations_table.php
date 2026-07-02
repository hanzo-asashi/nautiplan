<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('budget_realizations', function (Blueprint $table) {
            $table->string('realization_type', 50)->default('non_pengadaan')->after('activity_budget_id');
            $table->string('vendor_name', 255)->nullable()->after('amount');
            $table->text('vendor_address')->nullable()->after('vendor_name');
            $table->string('vendor_npwp', 50)->nullable()->after('vendor_address');
            $table->string('procurement_number', 100)->nullable()->after('vendor_npwp');
            $table->date('procurement_date')->nullable()->after('procurement_number');
            $table->string('sp2d_number', 100)->nullable()->after('procurement_date');
            $table->date('sp2d_date')->nullable()->after('sp2d_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_realizations', function (Blueprint $table) {
            $table->dropColumn([
                'realization_type',
                'vendor_name',
                'vendor_address',
                'vendor_npwp',
                'procurement_number',
                'procurement_date',
                'sp2d_number',
                'sp2d_date',
            ]);
        });
    }
};
