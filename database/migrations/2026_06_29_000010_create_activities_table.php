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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('renja_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft')->comment('draft, proposed, approved, in_progress, completed, cancelled');
            $table->string('priority')->default('medium')->comment('low, medium, high, critical');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->string('location')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['code', 'fiscal_year_id']);
            $table->index('status');
            $table->index('priority');
            $table->index(['program_id', 'fiscal_year_id']);
            $table->index('responsible_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
