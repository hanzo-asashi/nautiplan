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
        Schema::table('realization_items', function (Blueprint $table) {
            $table->foreignId('budget_item_id')->constrained('budget_items')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realization_items', function (Blueprint $table) {
            $table->dropForeign(['budget_item_id']);
            $table->dropColumn('budget_item_id');
        });
    }
};
