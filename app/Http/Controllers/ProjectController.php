<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    // LIST all projects
    public function index(Request $request)
    {
        $query = Project::withCount(['files', 'photos']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->latest()->paginate(10)->withQueryString();

        return view('projects.index', compact('projects'));
    }

    // SHOW create form
    public function create()
    {
        return view('projects.create');
    }

    // SAVE new project
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|max:100|unique:projects,code',
            'location'     => 'nullable|string|max:255',
            'province'     => 'nullable|string|max:100',
            'district'     => 'nullable|string|max:100',
            'area_hectare' => 'nullable|numeric|min:0',
            'description'  => 'nullable|string',
            'status'       => 'required|in:planned,active,completed,suspended',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
        ]);

        Project::create($validated);

        return redirect()
            ->route('projects.index')
            ->with('success', "Project '{$validated['name']}' created successfully.");
    }

    // SHOW project detail
    public function show(Project $project)
    {
        $project->load(['files', 'photos', 'kmlFiles']);
        return view('projects.show', compact('project'));
    }

    // SHOW edit form
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    // SAVE edits
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|max:100|unique:projects,code,' . $project->id,
            'location'     => 'nullable|string|max:255',
            'province'     => 'nullable|string|max:100',
            'district'     => 'nullable|string|max:100',
            'area_hectare' => 'nullable|numeric|min:0',
            'description'  => 'nullable|string',
            'status'       => 'required|in:planned,active,completed,suspended',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', "Project updated successfully.");
    }

    // DELETE project
    public function destroy(Project $project)
    {
        $name = $project->name;
        $project->delete(); // cascade deletes files + photos rows in DB

        return redirect()
            ->route('projects.index')
            ->with('success', "Project '{$name}' deleted.");
    }
}