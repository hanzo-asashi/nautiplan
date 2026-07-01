<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_documents', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('activity_id')->constrained('activity_documents')->onDelete('cascade');
            $table->integer('version')->default(1)->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_documents', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'version']);
        });
    }
};
