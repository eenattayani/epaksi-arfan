@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

{{-- ── Welcome Header ──────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Dashboard</h4>
        <p class="text-muted small mb-0">
            Geoportal Engineering - EPAKSI Irrigation Project Management System
            <span class="text-muted mx-2">·</span>
            {{ now()->format('l, d F Y') }}
        </p>
    </div>
    <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> New Project
    </a>
</div>

{{-- ── Stat Cards ───────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Total Projects --}}
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0"
             style="background:linear-gradient(135deg,#1a3c5e,#0f2540);color:#fff">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-2 fw-bold">{{ $stats['total'] }}</div>
                        <div class="small opacity-75 mt-1">Total Projects</div>
                    </div>
                    <div style="background:rgba(255,255,255,.15);border-radius:10px;
                                width:42px;height:42px;display:flex;
                                align-items:center;justify-content:center">
                        <i class="bi bi-folder2-open fs-5"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2" style="border-top:1px solid rgba(255,255,255,.1)">
                    <small class="opacity-60">
                        <i class="bi bi-map me-1"></i>
                        {{ number_format($stats['area'], 1) }} Ha total area
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Active --}}
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0"
             style="background:linear-gradient(135deg,#198754,#146c43);color:#fff">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-2 fw-bold">{{ $stats['active'] }}</div>
                        <div class="small opacity-75 mt-1">Active Projects</div>
                    </div>
                    <div style="background:rgba(255,255,255,.15);border-radius:10px;
                                width:42px;height:42px;display:flex;
                                align-items:center;justify-content:center">
                        <i class="bi bi-play-circle fs-5"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2" style="border-top:1px solid rgba(255,255,255,.1)">
                    <small class="opacity-60">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ $stats['completed'] }} completed
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- KML Files --}}
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0"
             style="background:linear-gradient(135deg,#0d6efd,#0a58ca);color:#fff">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-2 fw-bold">{{ $stats['kml_files'] }}</div>
                        <div class="small opacity-75 mt-1">KML Layers</div>
                    </div>
                    <div style="background:rgba(255,255,255,.15);border-radius:10px;
                                width:42px;height:42px;display:flex;
                                align-items:center;justify-content:center">
                        <i class="bi bi-layers fs-5"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2" style="border-top:1px solid rgba(255,255,255,.1)">
                    <small class="opacity-60">
                        <i class="bi bi-images me-1"></i>
                        {{ $stats['photos'] }} photos uploaded
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Planned --}}
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0"
             style="background:linear-gradient(135deg,#6c757d,#565e64);color:#fff">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-2 fw-bold">{{ $stats['planned'] }}</div>
                        <div class="small opacity-75 mt-1">Planned Projects</div>
                    </div>
                    <div style="background:rgba(255,255,255,.15);border-radius:10px;
                                width:42px;height:42px;display:flex;
                                align-items:center;justify-content:center">
                        <i class="bi bi-clock fs-5"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2" style="border-top:1px solid rgba(255,255,255,.1)">
                    <small class="opacity-60">
                        <i class="bi bi-pause-circle me-1"></i>
                        {{ $stats['suspended'] }} suspended
                    </small>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Charts + Recent ──────────────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- Status Chart --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-pie-chart text-primary me-2"></i>Projects by Status
                </h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="statusChart" style="max-height:220px"></canvas>
            </div>
        </div>
    </div>

    {{-- Province Chart --}}
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-bar-chart text-success me-2"></i>Projects by Province
                </h6>
            </div>
            <div class="card-body">
                @if($provinceCounts->isEmpty())
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-bar-chart fs-2 d-block mb-2 opacity-25"></i>
                        No province data yet. Edit projects to add province info.
                    </div>
                @else
                    <canvas id="provinceChart" style="max-height:220px"></canvas>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Recent Projects + Quick Links ───────────────── --}}
<div class="row g-4">

    {{-- Recent Projects --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-clock-history text-warning me-2"></i>Recent Projects
                </h6>
                <a href="{{ route('projects.index') }}"
                   class="btn btn-sm btn-outline-secondary">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentProjects->isEmpty())
                    <div class="text-center py-5 text-muted small">
                        <i class="bi bi-folder-x fs-2 d-block mb-2 opacity-25"></i>
                        No projects yet.
                    </div>
                @else
                <div class="list-group list-group-flush">
                    @foreach($recentProjects as $project)
                    @php
                        $colors = ['active'=>'success','completed'=>'primary',
                                   'suspended'=>'danger','planned'=>'secondary'];
                        $c = $colors[$project->status] ?? 'secondary';
                    @endphp
                    <div class="list-group-item px-4 py-3">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            {{-- Icon --}}
                            <div style="width:38px;height:38px;flex-shrink:0;
                                        background:#f0f4f8;border-radius:10px;
                                        display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-folder2 text-primary"></i>
                            </div>
                            {{-- Info --}}
                            <div style="flex:1;min-width:0">
                                <div class="fw-semibold small text-truncate">
                                    {{ $project->name }}
                                </div>
                                <div class="text-muted" style="font-size:.75rem">
                                    <span class="font-monospace">{{ $project->code }}</span>
                                    @if($project->district)
                                        <span class="mx-1">·</span>{{ $project->district }}
                                    @endif
                                </div>
                            </div>
                            {{-- Badge --}}
                            <span class="badge bg-{{ $c }}-subtle
                                         text-{{ $c }} border border-{{ $c }}-subtle"
                                  style="font-size:.7rem;white-space:nowrap">
                                {{ ucfirst($project->status) }}
                            </span>
                            {{-- Stats --}}
                            <div class="text-muted text-end" style="font-size:.75rem;white-space:nowrap">
                                <div><i class="bi bi-layers me-1"></i>{{ $project->files_count }} files</div>
                                <div><i class="bi bi-images me-1"></i>{{ $project->photos_count }} photos</div>
                            </div>
                            {{-- Actions --}}
                            <div class="d-flex gap-1">
                                <a href="{{ route('projects.show', $project) }}"
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($project->files_count > 0)
                                <a href="{{ route('projects.map', $project) }}"
                                   class="btn btn-sm btn-outline-success" title="Map">
                                    <i class="bi bi-map"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick Links + Summary --}}
    <div class="col-lg-4">

        {{-- Quick Actions --}}
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-lightning text-warning me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm text-start">
                    <i class="bi bi-plus-circle me-2"></i>Create New Project
                </a>
                <a href="{{ route('map.explorer') }}" class="btn btn-success btn-sm text-start">
                    <i class="bi bi-map me-2"></i>Open Map Explorer
                </a>
                <a href="{{ route('projects.index') }}?status=active"
                   class="btn btn-outline-secondary btn-sm text-start">
                    <i class="bi bi-play-circle me-2"></i>View Active Projects
                </a>
                <a href="{{ route('projects.index') }}?status=planned"
                   class="btn btn-outline-secondary btn-sm text-start">
                    <i class="bi bi-clock me-2"></i>View Planned Projects
                </a>
            </div>
        </div>

        {{-- System Summary --}}
        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle text-info me-2"></i>System Summary
                </h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-4">
                        <span class="text-muted">Total Projects</span>
                        <strong>{{ $stats['total'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-4">
                        <span class="text-muted">Total Area</span>
                        <strong>{{ number_format($stats['area'], 2) }} Ha</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-4">
                        <span class="text-muted">KML Layers</span>
                        <strong>{{ $stats['kml_files'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-4">
                        <span class="text-muted">Photos</span>
                        <strong>{{ $stats['photos'] }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-4">
                        <span class="text-muted">Last Updated</span>
                        <strong>{{ now()->format('d M Y') }}</strong>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Status Donut Chart ────────────────────────────
    const statusData = @json($statusCounts);
    const statusCtx  = document.getElementById('statusChart');

    if (statusCtx && Object.keys(statusData).length > 0) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData).map(s =>
                    s.charAt(0).toUpperCase() + s.slice(1)
                ),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: ['#198754','#0d6efd','#6c757d','#dc3545'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11 }, padding: 12 }
                    }
                },
                cutout: '65%',
            }
        });
    } else if (statusCtx) {
        statusCtx.parentElement.innerHTML =
            '<div class="text-center text-muted small py-4">No data yet</div>';
    }

    // ── Province Bar Chart ────────────────────────────
    const provinceData = @json($provinceCounts);
    const provinceCtx  = document.getElementById('provinceChart');

    if (provinceCtx && Object.keys(provinceData).length > 0) {
        new Chart(provinceCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(provinceData),
                datasets: [{
                    label: 'Projects',
                    data: Object.values(provinceData),
                    backgroundColor: '#1a3c5e',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 }
                        },
                        grid: { color: '#f0f0f0' }
                    },
                    x: {
                        ticks: { font: { size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

});
</script>
@endpush