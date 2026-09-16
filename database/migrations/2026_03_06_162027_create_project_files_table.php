<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained()
                  ->onDelete('cascade'); // Delete files when project deleted
            $table->string('file_name');          // Stored filename (hashed)
            $table->string('original_name');      // Original upload name
            $table->string('file_path');          // Relative path in storage
            $table->enum('file_type', ['kml', 'shp', 'document', 'other'])
                  ->default('other');
            $table->unsignedBigInteger('file_size')->nullable(); // Bytes
            $table->string('layer_color', 7)->default('#FF0000'); // Hex for map
            $table->boolean('is_visible')->default(true); // Toggle on map
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_files');
    }
};