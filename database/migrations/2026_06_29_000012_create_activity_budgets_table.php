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
        Schema::create('activity_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('budget_category')->comment('personnel, goods_services, capital, other');
            $table->string('description');
            $table->decimal('amount', 18, 2)->default(0)->comment('Amount in IDR');
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('version')->default(1);
            $table->timestamps();

            $table->index(['activity_id', 'fiscal_year_id']);
            $table->index('budget_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_budgets');
    }
};
