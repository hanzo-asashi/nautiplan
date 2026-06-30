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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('renstra_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->text('objective')->nullable();
            $table->string('status')->default('draft')->comment('draft, active, completed, cancelled');
            $table->string('priority')->default('medium')->comment('low, medium, high, critical');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('total_budget', 18, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['code', 'fiscal_year_id']);
            $table->index('status');
            $table->index('priority');
            $table->index(['unit_id', 'fiscal_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
