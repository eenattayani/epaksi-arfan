<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_files', function (Blueprint $table) {
            // Ubah enum — hanya kml dan document
            // Hapus 'shp' dan 'other' dari pilihan
            $table->enum('file_type', ['kml', 'document'])
                  ->default('kml')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_files', function (Blueprint $table) {
            $table->enum('file_type', ['kml', 'shp', 'document', 'other'])
                  ->default('other')
                  ->change();
        });
    }
};