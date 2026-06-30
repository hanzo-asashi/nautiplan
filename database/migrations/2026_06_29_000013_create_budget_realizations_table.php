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
        Schema::create('budget_realizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_budget_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 18, 2)->default(0)->comment('Realized amount in IDR');
            $table->date('realization_date');
            $table->string('description')->nullable();
            $table->string('receipt_number')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('realization_date');
            $table->index('activity_budget_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_realizations');
    }
};
