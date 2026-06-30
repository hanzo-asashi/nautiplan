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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->string('status')->default('pending')->comment('pending, in_review, approved, rejected, revision');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('requested_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
