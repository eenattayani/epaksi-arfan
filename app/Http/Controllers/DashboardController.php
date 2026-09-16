<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectPhoto;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stats ──────────────────────────────────────────
        $stats = [
            'total'     => Project::count(),
            'active'    => Project::where('status', 'active')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'planned'   => Project::where('status', 'planned')->count(),
            'suspended' => Project::where('status', 'suspended')->count(),
            'kml_files' => ProjectFile::where('file_type', 'kml')->count(),
            'photos'    => ProjectPhoto::count(),
            'area'      => Project::sum('area_hectare') ?? 0,
        ];

        // ── Recent projects ────────────────────────────────
        $recentProjects = Project::withCount(['files', 'photos'])
            ->latest()
            ->take(5)
            ->get();

        // ── Projects by status (for chart) ────────────────
        $statusCounts = Project::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Projects by province (for chart) ──────────────
        $provinceCounts = Project::selectRaw('province, count(*) as total')
            ->whereNotNull('province')
            ->groupBy('province')
            ->orderByDesc('total')
            ->take(6)
            ->pluck('total', 'province');

        return view('dashboard', compact(
            'stats',
            'recentProjects',
            'statusCounts',
            'provinceCounts'
        ));
    }

    public function mapExplorer()
    {
        // Load ALL projects that have at least one visible KML file
        $projects = Project::with(['kmlFiles' => function ($q) {
                        $q->where('is_visible', true);
                    }])
                    ->whereHas('kmlFiles')
                    ->get();

        // Build a flat array of all KML layers with project context
        $allLayers = [];
        foreach ($projects as $project) {
            foreach ($project->kmlFiles as $file) {
                $allLayers[] = [
                    'id'           => $file->id,
                    'project_id'   => $project->id,
                    'project_name' => $project->name,
                    'project_code' => $project->code,
                    'project_url'  => route('projects.show', $project),
                    'map_url'      => route('projects.map', $project),
                    'name'         => $file->original_name,
                    'url'          => $file->public_url,
                    'color'        => $file->layer_color,
                    'is_visible'   => true,
                ];
            }
        }

        return view('map-explorer', [
            'projects'  => $projects,
            'allLayers' => $allLayers,
            'apiKey'    => config('services.google_maps.key'),
        ]);
    }
}