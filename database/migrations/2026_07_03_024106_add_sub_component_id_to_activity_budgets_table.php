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
        Schema::table('activity_budgets', function (Blueprint $table) {
            $table->foreignId('sub_component_id')->nullable()->after('activity_id')->constrained('sub_components')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_budgets', function (Blueprint $table) {
            $table->dropForeign(['sub_component_id']);
            $table->dropColumn('sub_component_id');
        });
    }
};
