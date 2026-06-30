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
        Schema::create('program_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->decimal('target_value', 15, 2)->default(0);
            $table->decimal('actual_value', 15, 2)->nullable();
            $table->string('unit_of_measure')->default('persen');
            $table->timestamps();

            $table->unique(['program_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_indicators');
    }
};
