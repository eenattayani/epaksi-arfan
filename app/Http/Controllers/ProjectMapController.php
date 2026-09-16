<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ProjectMapController extends Controller
{
    /**
     * Show the full-screen map viewer for a project.
     *
     * We pass the project with its KML files already loaded.
     * The Blade view will encode them as JSON for JavaScript to consume.
     */
    public function show(Project $project)
    {
        // Only load KML files — SHP and documents don't render on Google Maps
        $kmlFiles = $project->kmlFiles()->get()->map(function ($file) {
            return [
                'id'          => $file->id,
                'name'        => $file->original_name,
                'url'         => $file->public_url,
                'color'       => $file->layer_color,
                'is_visible'  => $file->is_visible,
                'toggle_url'  => route('projects.files.toggle', [
                                    $file->project_id, $file->id
                                 ]),
            ];
        });

        return view('projects.map', [
            'project'  => $project,
            'kmlFiles' => $kmlFiles,
            'apiKey'   => config('services.google_maps.key'),
        ]);
    }
}