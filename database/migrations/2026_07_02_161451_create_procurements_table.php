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
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_budget_id')->constrained('activity_budgets')->onDelete('restrict');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('restrict');
            $table->string('title');
            $table->string('procurement_type', 50); // 'surat_pesanan' or 'spk'
            $table->string('document_number', 100);
            $table->date('document_date');
            $table->string('work_duration', 100)->nullable();
            $table->string('nota_dinas_number', 100)->nullable();
            $table->date('nota_dinas_date')->nullable();
            $table->string('ba_pl_number', 100)->nullable();
            $table->date('ba_pl_date')->nullable();
            $table->foreignId('ppk_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('kpa_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};
