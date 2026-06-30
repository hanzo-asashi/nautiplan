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
        Schema::create('activity_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('indicator_type')->default('ikk')->comment('iku (Indikator Kinerja Utama), ikk (Indikator Kinerja Kegiatan)');
            $table->decimal('target_value', 15, 2)->default(0);
            $table->decimal('actual_value', 15, 2)->nullable();
            $table->string('unit_of_measure')->default('persen');
            $table->string('quarter')->default('annual')->comment('Q1, Q2, Q3, Q4, annual');
            $table->timestamps();

            $table->unique(['activity_id', 'code', 'quarter']);
            $table->index('indicator_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_indicators');
    }
};
