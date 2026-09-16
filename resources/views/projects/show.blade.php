@extends('layouts.app')

@section('title', $project->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('projects.index') }}">Projects</a>
    </li>
    <li class="breadcrumb-item active">{{ $project->code }}</li>
@endsection

@section('content')

{{-- ── Header ──────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0">{{ $project->name }}</h4>
                @php
                    $colors = ['active'=>'success','completed'=>'primary',
                               'suspended'=>'danger','planned'=>'secondary'];
                    $c = $colors[$project->status] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $c }}-subtle text-{{ $c }}
                             border border-{{ $c }}-subtle" style="font-size:.75rem">
                    {{ ucfirst($project->status) }}
                </span>
            </div>
            <span class="text-muted small font-monospace">{{ $project->code }}</span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form method="POST" action="{{ route('projects.destroy', $project) }}"
              onsubmit="return confirm('Delete this project and ALL its files?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
        </form>
    </div>
</div>

{{-- ── Body ─────────────────────────────────────────── --}}
<div class="row g-4">

{{-- ════════════════════════════════════════════════════
     LEFT COLUMN — Project info
     ════════════════════════════════════════════════════ --}}
<div class="col-lg-4">

    {{-- Info Card --}}
    <div class="card mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-info-circle text-primary me-2"></i>Project Details
            </h6>
        </div>
        <div class="card-body">
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted fw-normal">Location</dt>
                <dd class="col-7">{{ $project->location ?: '—' }}</dd>

                <dt class="col-5 text-muted fw-normal">District</dt>
                <dd class="col-7">{{ $project->district ?: '—' }}</dd>

                <dt class="col-5 text-muted fw-normal">Province</dt>
                <dd class="col-7">{{ $project->province ?: '—' }}</dd>

                <dt class="col-5 text-muted fw-normal">Area</dt>
                <dd class="col-7">
                    {{ $project->area_hectare ? number_format($project->area_hectare,2).' Ha' : '—' }}
                </dd>

                <dt class="col-5 text-muted fw-normal">Start</dt>
                <dd class="col-7">{{ $project->start_date?->format('d M Y') ?? '—' }}</dd>

                <dt class="col-5 text-muted fw-normal">End</dt>
                <dd class="col-7">{{ $project->end_date?->format('d M Y') ?? '—' }}</dd>

                <dt class="col-5 text-muted fw-normal">GIS Files</dt>
                <dd class="col-7">
                    <span class="badge bg-light text-dark border">
                        {{ $project->files->count() }} file(s)
                    </span>
                </dd>

                <dt class="col-5 text-muted fw-normal">Photos</dt>
                <dd class="col-7">
                    <span class="badge bg-light text-dark border">
                        {{ $project->photos->count() }} photo(s)
                    </span>
                </dd>
            </dl>
        </div>
    </div>

    {{-- Description --}}
    @if($project->description)
    <div class="card mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-text-paragraph text-secondary me-2"></i>Description
            </h6>
        </div>
        <div class="card-body small text-muted" style="white-space:pre-line">
            {{ $project->description }}
        </div>
    </div>
    @endif

    {{-- Quick Actions --}}
    <div class="card">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-lightning text-warning me-2"></i>Quick Actions
            </h6>
        </div>
        <div class="card-body d-grid gap-2">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                    data-bs-target="#uploadFileModal">
                <i class="bi bi-upload me-1"></i> Upload GIS File
            </button>
            <button class="btn btn-warning btn-sm text-dark" data-bs-toggle="modal"
                    data-bs-target="#uploadPhotoModal">
                <i class="bi bi-camera me-1"></i> Upload Photos
            </button>
            <a href="{{ route('projects.map', $project) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-map me-1"></i> View on Map
                <span class="badge bg-white text-primary ms-1" style="font-size:.6rem">Stage 4</span>
            </a>
        </div>
    </div>

</div>

{{-- ════════════════════════════════════════════════════
     RIGHT COLUMN — Files & Photos
     ════════════════════════════════════════════════════ --}}
<div class="col-lg-8">

    {{-- ── GIS FILES ──────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-layers text-success me-2"></i>
                GIS Files
                <span class="badge bg-secondary ms-1">{{ $project->files->count() }}</span>
            </h6>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                    data-bs-target="#uploadFileModal">
                <i class="bi bi-plus-lg me-1"></i> Upload
            </button>
        </div>

        <div class="card-body p-0">
            @if($project->files->isEmpty())
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-cloud-upload fs-2 d-block mb-2 opacity-50"></i>
                    No GIS files uploaded yet.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">File</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Visible</th>
                            <th>Color</th>
                            <th class="pe-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->files as $file)
                        <tr>
                            <td class="ps-3">
                                @php
                                    $icons = ['kml'=>'bi-globe text-success',
                                              'shp'=>'bi-layers text-primary',
                                              'document'=>'bi-file-earmark-text text-warning'];
                                    $icon = $icons[$file->file_type] ?? 'bi-file text-secondary';
                                @endphp
                                <i class="bi {{ $icon }} me-1"></i>
                                <span title="{{ $file->original_name }}">
                                    {{ Str::limit($file->original_name, 30) }}
                                </span>
                                @if($file->notes)
                                    <br><small class="text-muted">{{ $file->notes }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase">
                                    {{ $file->file_type }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $file->file_size_human }}</td>
                            <td>
                                {{-- Toggle switch — only meaningful for KML --}}
                                @if($file->file_type === 'kml')
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input layer-toggle"
                                           type="checkbox"
                                           {{ $file->is_visible ? 'checked' : '' }}
                                           data-file-id="{{ $file->id }}"
                                           data-url="{{ route('projects.files.toggle', [$project, $file]) }}"
                                           title="Toggle layer visibility">
                                </div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($file->file_type === 'kml')
                                <input type="color"
                                       class="form-control form-control-color layer-color p-0 border-0"
                                       value="{{ $file->layer_color }}"
                                       data-file-id="{{ $file->id }}"
                                       data-url="{{ route('projects.files.color', [$project, $file]) }}"
                                       title="Change layer color"
                                       style="width:28px;height:28px;cursor:pointer">
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ $file->public_url }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('projects.files.destroy', [$project, $file]) }}"
                                          onsubmit="return confirm('Delete this file?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- ── PHOTOS ──────────────────────────────────── --}}
    <div class="card">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-images text-warning me-2"></i>
                Photos
                <span class="badge bg-secondary ms-1">{{ $project->photos->count() }}</span>
            </h6>
            <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal"
                    data-bs-target="#uploadPhotoModal">
                <i class="bi bi-plus-lg me-1"></i> Upload
            </button>
        </div>

        <div class="card-body">
            @if($project->photos->isEmpty())
                <div class="text-center py-3 text-muted small">
                    <i class="bi bi-images fs-2 d-block mb-2 opacity-50"></i>
                    No photos uploaded yet.
                </div>
            @else
            <div class="row g-2">
                @foreach($project->photos as $photo)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="position-relative" style="aspect-ratio:1;overflow:hidden;border-radius:8px">
                        <img src="{{ $photo->public_url }}"
                             alt="{{ $photo->caption ?? 'Project photo' }}"
                             class="w-100 h-100"
                             style="object-fit:cover;cursor:pointer"
                             onclick="openLightbox('{{ $photo->public_url }}', '{{ $photo->caption }}')">
                        {{-- Delete overlay --}}
                        <div class="position-absolute top-0 end-0 p-1">
                            <form method="POST"
                                  action="{{ route('projects.photos.destroy', [$project, $photo]) }}"
                                  onsubmit="return confirm('Delete this photo?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger rounded-circle"
                                        style="width:26px;height:26px;padding:0;font-size:.7rem"
                                        title="Delete">
                                    <i class="bi bi-x"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @if($photo->caption)
                    <small class="text-muted d-block mt-1 text-truncate px-1">
                        {{ $photo->caption }}
                    </small>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>{{-- end right col --}}
</div>{{-- end row --}}

{{-- ════════════════════════════════════════════════════
     MODALS
     ════════════════════════════════════════════════════ --}}

{{-- Upload GIS File Modal --}}
<div class="modal fade" id="uploadFileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-upload me-2 text-success"></i>Upload GIS File
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST"
                  action="{{ route('projects.files.store', $project) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    {{-- Drop Zone --}}
                    <div id="dropZone"
                         class="border-2 border-dashed rounded-3 text-center p-4 mb-3"
                         style="border:2px dashed #dee2e6;cursor:pointer;transition:.2s"
                         onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-cloud-upload fs-2 text-muted"></i>
                        <p class="mb-0 small text-muted mt-1">
                            Click or drag file here<br>
                            <span class="text-muted" style="font-size:.75rem">
                                KML: .kml | SHP: .shp .zip | Docs: .pdf .docx .xlsx
                            </span>
                        </p>
                    </div>
                    <input type="file" id="fileInput" name="file" class="d-none"
                           accept=".kml,.shp,.zip,.dbf,.prj,.shx,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                    <div id="selectedFileName" class="small text-success mb-3 d-none">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        <span></span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium small">File Type <span class="text-danger">*</span></label>
                            <select name="file_type" id="fileTypeSelect" class="form-select form-select-sm">
                                <option value="kml">KML — Map Layer</option>
                                <option value="shp">SHP — Shapefile</option>
                                <option value="document">Document (PDF/Office)</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="colorPickerGroup">
                            <label class="form-label fw-medium small">Layer Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="layer_color" value="#FF0000"
                                       class="form-control form-control-color"
                                       style="width:40px;height:32px">
                                <small class="text-muted">Shown on map</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium small">Notes (optional)</label>
                            <input type="text" name="notes" class="form-control form-control-sm"
                                   placeholder="e.g. Main canal network layer">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-upload me-1"></i> Upload File
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Upload Photos Modal --}}
<div class="modal fade" id="uploadPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-camera me-2 text-warning"></i>Upload Photos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST"
                  action="{{ route('projects.photos.store', $project) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label fw-medium small">
                            Select Photos <span class="text-danger">*</span>
                            <span class="text-muted">(up to 10, max 5MB each)</span>
                        </label>
                        <input type="file" name="photos[]" class="form-control form-control-sm"
                               multiple accept="image/jpeg,image/png,image/webp"
                               id="photoInput">
                    </div>

                    {{-- Preview strip --}}
                    <div id="photoPreview" class="d-flex gap-2 flex-wrap mb-3"></div>

                    <div>
                        <label class="form-label fw-medium small">Caption (optional)</label>
                        <input type="text" name="caption" class="form-control form-control-sm"
                               placeholder="e.g. Kondisi saluran primer, Jan 2024">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark btn-sm">
                        <i class="bi bi-upload me-1"></i> Upload Photos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Lightbox --}}
<div class="modal fade" id="lightboxModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-body p-2 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                        data-bs-dismiss="modal"></button>
                <img id="lightboxImg" src="" alt=""
                     class="img-fluid rounded" style="max-height:80vh">
                <p id="lightboxCaption" class="text-white-50 small mt-2 mb-0"></p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Drag & Drop Upload ────────────────────────────────
const dropZone   = document.getElementById('dropZone');
const fileInput  = document.getElementById('fileInput');
const nameDisplay = document.getElementById('selectedFileName');

fileInput?.addEventListener('change', () => {
    if (fileInput.files.length) {
        nameDisplay.querySelector('span').textContent = fileInput.files[0].name;
        nameDisplay.classList.remove('d-none');
        dropZone.style.borderColor = '#198754';
    }
});

dropZone?.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.style.background = '#f0fff4';
    dropZone.style.borderColor = '#198754';
});

dropZone?.addEventListener('dragleave', () => {
    dropZone.style.background = '';
    dropZone.style.borderColor = '#dee2e6';
});

dropZone?.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.style.background = '';
    const dt = e.dataTransfer;
    if (dt.files.length) {
        fileInput.files = dt.files;
        nameDisplay.querySelector('span').textContent = dt.files[0].name;
        nameDisplay.classList.remove('d-none');
        dropZone.style.borderColor = '#198754';
    }
});

// Show/hide color picker for non-KML types
document.getElementById('fileTypeSelect')?.addEventListener('change', function () {
    document.getElementById('colorPickerGroup').style.display =
        this.value === 'kml' ? 'block' : 'none';
});

// ── Photo Preview ─────────────────────────────────────
document.getElementById('photoInput')?.addEventListener('change', function () {
    const preview = document.getElementById('photoPreview');
    preview.innerHTML = '';
    [...this.files].slice(0, 10).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width:60px;height:60px;object-fit:cover;border-radius:6px';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});

// ── Layer Toggle (AJAX) ───────────────────────────────
document.querySelectorAll('.layer-toggle').forEach(toggle => {
    toggle.addEventListener('change', function () {
        fetch(this.dataset.url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            // Visual feedback
            const row = this.closest('tr');
            row.style.opacity = data.is_visible ? '1' : '0.45';
        });
    });
});

// ── Layer Color (AJAX) ────────────────────────────────
document.querySelectorAll('.layer-color').forEach(picker => {
    picker.addEventListener('change', function () {
        fetch(this.dataset.url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ color: this.value })
        });
    });
});

// ── Lightbox ──────────────────────────────────────────
function openLightbox(src, caption) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightboxCaption').textContent = caption ?? '';
    new bootstrap.Modal(document.getElementById('lightboxModal')).show();
}

// ── Re-open modal if validation failed ────────────────
@if($errors->any())
    // Re-open whichever modal had the error
    const modal = new bootstrap.Modal(document.getElementById('uploadFileModal'));
    modal.show();
@endif
</script>
@endpush