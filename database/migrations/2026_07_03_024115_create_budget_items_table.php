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
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_budget_id')->constrained('activity_budgets')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('volume', 15, 2)->default(1);
            $table->string('unit');
            $table->decimal('unit_price', 18, 2);
            $table->decimal('total', 18, 2);
            $table->timestamps();

            $table->index('activity_budget_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
