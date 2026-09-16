<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();        // e.g. EPAKSI-2024-001
            $table->string('location')->nullable();  // Village/area name
            $table->string('province')->nullable();
            $table->string('district')->nullable();  // Kabupaten
            $table->decimal('area_hectare', 10, 2)->nullable(); // Luas area
            $table->text('description')->nullable();
            $table->enum('status', ['planned', 'active', 'completed', 'suspended'])
                  ->default('planned');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('thumbnail')->nullable();  // Main project photo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};