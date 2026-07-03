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
            $table->string('spp_number')->nullable()->after('sp2d_date');
            $table->date('spp_date')->nullable()->after('spp_number');
            $table->string('spm_number')->nullable()->after('spp_date');
            $table->date('spm_date')->nullable()->after('spm_number');
            $table->string('sptjb_number')->nullable()->after('spm_date');
            $table->date('sptjb_date')->nullable()->after('sptjb_number');
        });

        Schema::table('realization_items', function (Blueprint $table) {
            $table->decimal('tax_pph22', 18, 2)->default(0)->after('tax_pph21_mixed');
            $table->decimal('tax_pph23', 18, 2)->default(0)->after('tax_pph22');
            $table->decimal('tax_ppn', 18, 2)->default(0)->after('tax_pph23');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_realizations', function (Blueprint $table) {
            $table->dropColumn([
                'spp_number',
                'spp_date',
                'spm_number',
                'spm_date',
                'sptjb_number',
                'sptjb_date',
            ]);
        });

        Schema::table('realization_items', function (Blueprint $table) {
            $table->dropColumn([
                'tax_pph22',
                'tax_pph23',
                'tax_ppn',
            ]);
        });
    }
};
