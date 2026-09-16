<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPhoto extends Model
{
    protected $fillable = [
        'project_id', 'file_name', 'file_path', 'caption', 'sort_order',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getPublicUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}