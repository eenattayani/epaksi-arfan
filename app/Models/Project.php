<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'location', 'province', 'district',
        'area_hectare', 'description', 'status',
        'start_date', 'end_date', 'thumbnail',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // One project → many KML/SHP files
    public function files()
    {
        return $this->hasMany(ProjectFile::class)->orderBy('sort_order');
    }

    // Convenience: only KML files (used for map rendering)
    public function kmlFiles()
    {
        return $this->hasMany(ProjectFile::class)
                    ->where('file_type', 'kml')
                    ->orderBy('sort_order');
    }

    // One project → many photos
    public function photos()
    {
        return $this->hasMany(ProjectPhoto::class)->orderBy('sort_order');
    }

    // Status badge color helper (used in Blade)
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active'    => 'success',
            'completed' => 'primary',
            'suspended' => 'danger',
            default     => 'secondary', // planned
        };
    }
}