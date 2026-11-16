<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectFileController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'file'         => 'required|file|max:51200',
            'file_type'    => 'required|in:kml,document',
            'notes'        => 'nullable|string|max:500',
            'layer_color'  => 'nullable|string|max:7',
        ]);

        $file      = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $fileType  = $request->file_type;

        // ── Validasi ekstensi per tipe ─────────────────────
        $allowedExtensions = [
            'kml'      => ['kml'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
        ];

        if (!in_array($extension, $allowedExtensions[$fileType])) {
            $allowed = implode(', ', $allowedExtensions[$fileType]);
            return back()->with('error',
                "File tidak valid untuk tipe {$fileType}. " .
                "Ekstensi yang diizinkan: {$allowed}"
            );
        }

        // ── Simpan file ────────────────────────────────────
        $storedName = Str::slug($project->code) . '_' . time() . '.' . $extension;
        $directory  = "projects/{$project->id}/files";
        $path       = $file->storeAs($directory, $storedName, 'public');

        $sortOrder = ProjectFile::where('project_id', $project->id)
                                ->max('sort_order') + 1;

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

        return back()->with('success',
            "File '{$file->getClientOriginalName()}' berhasil diupload."
        );
    }

    public function destroy(Project $project, ProjectFile $file)
    {
        // Authorization: hanya admin/editor yang dapat menghapus
        abort_unless(auth()->user()->canEdit(), 403);
        abort_if($file->project_id !== $project->id, 403);
        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return back()->with('success', "File '{$file->original_name}' dihapus.");
    }

    public function toggle(Project $project, ProjectFile $file)
    {
        // Authorization: hanya admin/editor yang dapat mengubah visibilitas
        abort_unless(auth()->user()->canEdit(), 403);
        abort_if($file->project_id !== $project->id, 403);
        $file->update(['is_visible' => !$file->is_visible]);

        return response()->json([
            'success'    => true,
            'is_visible' => $file->is_visible,
        ]);
    }

    public function color(Request $request, Project $project, ProjectFile $file)
    {
        abort_if($file->project_id !== $project->id, 403);
        $request->validate(['color' => 'required|string|max:7']);
        $file->update(['layer_color' => $request->color]);

        return response()->json(['success' => true]);
    }

    /**
     * Tampilkan file PDF di browser viewer.
     * Hanya untuk file bertipe 'document' dengan ekstensi pdf.
     */
    public function view(Project $project, ProjectFile $file)
    {
        // Safety checks
        abort_if($file->project_id !== $project->id, 403);
        abort_if($file->file_type !== 'document', 404);

        $extension = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION));
        abort_if($extension !== 'pdf', 404, 'Hanya file PDF yang bisa ditampilkan.');

        // Pastikan file fisik ada di storage
        abort_unless(
            Storage::disk('public')->exists($file->file_path),
            404,
            'File tidak ditemukan di storage.'
        );

        return view('projects.file-viewer', [
            'project' => $project,
            'file'    => $file,
        ]);
    }
}