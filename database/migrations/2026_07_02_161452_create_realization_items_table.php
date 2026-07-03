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
        Schema::create('realization_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_realization_id')->constrained('budget_realizations')->onDelete('cascade');
            $table->string('name');
            $table->decimal('volume', 15, 2)->default(1);
            $table->string('unit', 50);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('tax_pph21', 18, 2)->default(0);
            $table->boolean('tax_pph21_mixed')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realization_items');
    }
};
