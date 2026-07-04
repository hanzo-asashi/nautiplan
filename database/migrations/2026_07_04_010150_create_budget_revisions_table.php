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
        Schema::create('budget_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_budget_id')->constrained('activity_budgets')->onDelete('cascade');
            $table->integer('revision_number');
            $table->text('description');
            $table->decimal('amount_semula', 18, 2);
            $table->decimal('amount_menjadi', 18, 2);
            $table->foreignId('revised_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_revisions');
    }
};
