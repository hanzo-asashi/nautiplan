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
        Schema::create('budget_revision_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_revision_id')->constrained('budget_revisions')->onDelete('cascade');
            $table->foreignId('budget_item_id')->nullable()->constrained('budget_items')->onDelete('set null');

            // Semula (Old state)
            $table->string('name_semula')->nullable();
            $table->decimal('volume_semula', 15, 2)->default(0);
            $table->string('unit_semula', 50)->nullable();
            $table->decimal('unit_price_semula', 18, 2)->default(0);
            $table->decimal('total_semula', 18, 2)->default(0);

            // Menjadi (New state)
            $table->string('name_menjadi')->nullable();
            $table->decimal('volume_menjadi', 15, 2)->default(0);
            $table->string('unit_menjadi', 50)->nullable();
            $table->decimal('unit_price_menjadi', 18, 2)->default(0);
            $table->decimal('total_menjadi', 18, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_revision_details');
    }
};
