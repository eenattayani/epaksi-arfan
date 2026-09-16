<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectFileController extends Controller
{
    /**
     * Upload a KML, SHP, or document file.
     * Files are stored at: storage/app/public/projects/{project_id}/files/
     * This path becomes publicly accessible via the symlink we created earlier.
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'file'       => 'required|file|max:51200', // 50MB max
            'file_type'  => 'required|in:kml,shp,document,other',
            'notes'      => 'nullable|string|max:500',
            'layer_color'=> 'nullable|string|max:7',
        ]);

        // Validate file extension based on type
        $file      = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $fileType  = $request->file_type;

        $allowedExtensions = [
            'kml'      => ['kml'],
            'shp'      => ['shp', 'zip', 'dbf', 'prj', 'shx'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
            'other'    => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt',
                           'kml', 'shp', 'zip'],
        ];

        if (!in_array($extension, $allowedExtensions[$fileType])) {
            return back()
                ->with('error', "Invalid file type. Allowed for {$fileType}: "
                    . implode(', ', $allowedExtensions[$fileType]));
        }

        // Build a clean stored filename: {project_code}_{timestamp}.{ext}
        $storedName = Str::slug($project->code) . '_' . time() . '.' . $extension;
        $directory  = "projects/{$project->id}/files";

        // Store in storage/app/public/projects/{id}/files/
        $path = $file->storeAs($directory, $storedName, 'public');

        // Determine sort order (append to end)
        $sortOrder = ProjectFile::where('project_id', $project->id)->max('sort_order') + 1;

        ProjectFile::create([
            'project_id'    => $project->id,
            'file_name'     => $storedName,
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $path,
            'file_type'     => $fileType,
            'file_size'     => $file->getSize(),
            'layer_color'   => $request->layer_color ?? '#FF0000',
            'is_visible'    => true,
            'sort_order'    => $sortOrder,
            'notes'         => $request->notes,
        ]);

        return back()->with('success', "File '{$file->getClientOriginalName()}' uploaded successfully.");
    }

    /**
     * Delete a file — removes DB record AND the actual file from disk.
     */
    public function destroy(Project $project, ProjectFile $file)
    {
        // Safety check: file must belong to this project
        abort_if($file->project_id !== $project->id, 403);

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return back()->with('success', "File '{$file->original_name}' deleted.");
    }

    /**
     * Toggle KML layer visibility on/off (called via AJAX from the map page).
     */
    public function toggle(Project $project, ProjectFile $file)
    {
        abort_if($file->project_id !== $project->id, 403);

        $file->update(['is_visible' => !$file->is_visible]);

        return response()->json([
            'success'    => true,
            'is_visible' => $file->is_visible,
        ]);
    }

    /**
     * Update KML layer color.
     */
    public function color(Request $request, Project $project, ProjectFile $file)
    {
        abort_if($file->project_id !== $project->id, 403);

        $request->validate(['color' => 'required|string|max:7']);
        $file->update(['layer_color' => $request->color]);

        return response()->json(['success' => true]);
    }
}