<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectPhotoController extends Controller
{
    /**
     * Upload a project photo.
     * Stored at: storage/app/public/projects/{project_id}/photos/
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'photos'    => 'required|array|min:1|max:10',    // up to 10 at once
            'photos.*'  => 'image|mimes:jpg,jpeg,png,webp|max:5120', // 5MB each
            'caption'   => 'nullable|string|max:255',
        ]);

        $directory = "projects/{$project->id}/photos";
        $sortStart = ProjectPhoto::where('project_id', $project->id)->max('sort_order') + 1;

        foreach ($request->file('photos') as $index => $photo) {
            $storedName = Str::slug($project->code) . '_photo_' . time() . "_{$index}."
                        . strtolower($photo->getClientOriginalExtension());

            $path = $photo->storeAs($directory, $storedName, 'public');

            ProjectPhoto::create([
                'project_id' => $project->id,
                'file_name'  => $storedName,
                'file_path'  => $path,
                'caption'    => $request->caption,
                'sort_order' => $sortStart + $index,
            ]);
        }

        $count = count($request->file('photos'));
        return back()->with('success', "{$count} photo(s) uploaded successfully.");
    }

    /**
     * Delete a single photo.
     */
    public function destroy(Project $project, ProjectPhoto $photo)
    {
        abort_if($photo->project_id !== $project->id, 403);

        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return back()->with('success', 'Photo deleted.');
    }
}