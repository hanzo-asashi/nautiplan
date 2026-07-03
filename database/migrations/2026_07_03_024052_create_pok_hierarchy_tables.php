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
        Schema::create('outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->timestamps();

            $table->index(['activity_id', 'code']);
        });

        Schema::create('sub_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('output_id')->constrained('outputs')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->timestamps();

            $table->index(['output_id', 'code']);
        });

        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_output_id')->constrained('sub_outputs')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->timestamps();

            $table->index(['sub_output_id', 'code']);
        });

        Schema::create('sub_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained('components')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->timestamps();

            $table->index(['component_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_components');
        Schema::dropIfExists('components');
        Schema::dropIfExists('sub_outputs');
        Schema::dropIfExists('outputs');
    }
};
