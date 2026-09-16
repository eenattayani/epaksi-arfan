<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id', 'file_name', 'original_name', 'file_path',
        'file_type', 'file_size', 'layer_color', 'is_visible',
        'sort_order', 'notes',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Returns the full public URL — Google Maps needs this to load KML!
    public function getPublicUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    // Human-readable file size
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}