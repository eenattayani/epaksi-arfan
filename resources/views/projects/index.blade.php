@extends('layouts.app')

@section('title', 'Projects')

@section('breadcrumb')
    <li class="breadcrumb-item active">Projects</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Projects</h4>
        <p class="text-muted small mb-0">Manage all EPAKSI irrigation projects</p>
    </div>
    <a href="{{ route('projects.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Project
    </a>
</div>

{{-- ── Stats Row ──────────────────────────────────── --}}
@php
    $allProjects = \App\Models\Project::selectRaw('status, count(*) as total')
                    ->groupBy('status')->pluck('total','status');
@endphp
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Total Projects','icon'=>'folder2-open','color'=>'#1a3c5e',
         'val' => \App\Models\Project::count()],
        ['label'=>'Active','icon'=>'play-circle','color'=>'#198754',
         'val' => $allProjects['active'] ?? 0],
        ['label'=>'Completed','icon'=>'check-circle','color'=>'#0d6efd',
         'val' => $allProjects['completed'] ?? 0],
        ['label'=>'Planned','icon'=>'clock','color'=>'#6c757d',
         'val' => $allProjects['planned'] ?? 0],
    ] as $stat)
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:{{ $stat['color'] }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-3 fw-bold">{{ $stat['val'] }}</div>
                    <div class="small opacity-75">{{ $stat['label'] }}</div>
                </div>
                <i class="bi bi-{{ $stat['icon'] }} fs-3 opacity-50"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Filter / Search ────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('projects.index') }}" class="row g-2">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control border-start-0"
                           placeholder="Search by name, code, location…">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach(['planned','active','completed','suspended'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if(request()->hasAny(['search','status']))
                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Projects Table ──────────────────────────────── --}}
<div class="card">
    <div class="card-body p-0">
        @if($projects->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-folder-x text-muted" style="font-size:3rem"></i>
            <p class="text-muted mt-2">No projects found.</p>
            <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Create First Project
            </a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Project</th>
                        <th>Lokasi</th>
                        <th>Luas (Ha)</th>
                        <th>File</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $i => $project)
                    <tr>
                        <td class="ps-4 text-muted small">
                            {{ $projects->firstItem() + $i }}
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $project->name }}</div>
                            <small class="text-muted font-monospace">{{ $project->code }}</small>
                        </td>
                        <td class="small text-muted">
                            {{ $project->district }}{{ $project->province ? ', '.$project->province : '' }}
                        </td>
                        <td class="small">
                            {{ $project->area_hectare ? number_format($project->area_hectare, 2) : '—' }}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-paperclip"></i> {{ $project->files_count }}
                            </span>
                        </td>
                        <td>
                            @php
                                $colors = ['active'=>'success','completed'=>'primary',
                                           'suspended'=>'danger','planned'=>'secondary'];
                                $color  = $colors[$project->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}-subtle
                                         text-{{ $color }} border border-{{ $color }}-subtle badge-status">
                                {{ ucfirst($project->status) }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            {{ $project->start_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('projects.show', $project) }}"
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(Auth::user()->canEdit())
                                <a href="{{ route('projects.edit', $project) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif

                                @if(Auth::user()->canDelete())
                                <form method="POST"
                                      action="{{ route('projects.destroy', $project) }}"
                                      onsubmit="return confirm('Delete this project?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($projects->hasPages())
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
            <small class="text-muted">
                Showing {{ $projects->firstItem() }}–{{ $projects->lastItem() }}
                of {{ $projects->total() }} projects
            </small>
            {{ $projects->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection