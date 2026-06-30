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
        Schema::create('renstra_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renstra_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->decimal('target_value', 15, 2)->default(0);
            $table->string('unit_of_measure')->default('persen');
            $table->decimal('baseline_value', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['renstra_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renstra_indicators');
    }
};
