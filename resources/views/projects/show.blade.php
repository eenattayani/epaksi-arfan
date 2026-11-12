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
        <a href="{{ route('projects.edit', $project) }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form method="POST" action="{{ route('projects.destroy', $project) }}"
              onsubmit="return confirm('Hapus project ini beserta semua filenya?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i>Hapus
            </button>
        </form>
    </div>
</div>

<div class="row g-4">

{{-- ════════════════════════════════════════════════════
     LEFT COLUMN
     ════════════════════════════════════════════════════ --}}
<div class="col-lg-4">

    {{-- Info Card --}}
    <div class="card mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-info-circle text-primary me-2"></i>Detail Project
            </h6>
        </div>
        <div class="card-body">
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted fw-normal">Lokasi</dt>
                <dd class="col-7">{{ $project->location ?: '—' }}</dd>

                <dt class="col-5 text-muted fw-normal">Kabupaten</dt>
                <dd class="col-7">{{ $project->district ?: '—' }}</dd>

                <dt class="col-5 text-muted fw-normal">Provinsi</dt>
                <dd class="col-7">{{ $project->province ?: '—' }}</dd>

                <dt class="col-5 text-muted fw-normal">Luas Area</dt>
                <dd class="col-7">
                    {{ $project->area_hectare
                        ? number_format($project->area_hectare, 2) . ' Ha'
                        : '—' }}
                </dd>

                <dt class="col-5 text-muted fw-normal">Mulai</dt>
                <dd class="col-7">
                    {{ $project->start_date?->format('d M Y') ?? '—' }}
                </dd>

                <dt class="col-5 text-muted fw-normal">Selesai</dt>
                <dd class="col-7">
                    {{ $project->end_date?->format('d M Y') ?? '—' }}
                </dd>

                <dt class="col-5 text-muted fw-normal">KML Layers</dt>
                <dd class="col-7">
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        {{ $project->kmlFiles->count() }} layer
                    </span>
                </dd>

                <dt class="col-5 text-muted fw-normal">Dokumen</dt>
                <dd class="col-7">
                    <span class="badge bg-light text-dark border">
                        {{ $project->files->where('file_type','document')->count() }} file
                    </span>
                </dd>

                <dt class="col-5 text-muted fw-normal">Foto</dt>
                <dd class="col-7">
                    <span class="badge bg-light text-dark border">
                        {{ $project->photos->count() }} foto
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
                <i class="bi bi-text-paragraph text-secondary me-2"></i>Deskripsi
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
            @if(Auth::user()->canEdit())
            <button class="btn btn-success btn-sm text-start"
                    data-bs-toggle="modal" data-bs-target="#uploadKmlModal">
                <i class="bi bi-globe me-2"></i>Upload KML Layer
            </button>
            <button class="btn btn-outline-primary btn-sm text-start"
                    data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                <i class="bi bi-file-earmark-text me-2"></i>Upload Dokumen
            </button>
            <button class="btn btn-warning text-dark btn-sm text-start"
                    data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                <i class="bi bi-camera me-2"></i>Upload Foto
            </button>
            @endif
            @if($project->kmlFiles->count() > 0)
            <a href="{{ route('projects.map', $project) }}"
               class="btn btn-primary btn-sm text-start">
                <i class="bi bi-map me-2"></i>Buka di Peta
            </a>
            @endif
        </div>
    </div>

</div>

{{-- ════════════════════════════════════════════════════
     RIGHT COLUMN
     ════════════════════════════════════════════════════ --}}
<div class="col-lg-8">

    {{-- ── KML LAYERS ──────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-globe text-success me-2"></i>
                KML Layers
                <span class="badge bg-success ms-1">
                    {{ $project->kmlFiles->count() }}
                </span>
            </h6>
            @if(Auth::user()->canEdit())
            <button class="btn btn-sm btn-success"
                    data-bs-toggle="modal" data-bs-target="#uploadKmlModal">
                <i class="bi bi-plus-lg me-1"></i>Upload KML
            </button>
            @endif
        </div>
        <div class="card-body p-0">
            @if($project->kmlFiles->isEmpty())
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-globe fs-2 d-block mb-2 opacity-25"></i>
                    Belum ada KML layer. Upload file .kml untuk ditampilkan di peta.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nama File</th>
                            <th>Ukuran</th>
                            <th>Warna Panel</th>
                            <th>Tampil di Peta</th>
                            <th class="pe-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->kmlFiles as $file)
                        <tr>
                            <td class="ps-3">
                                <i class="bi bi-globe text-success me-1"></i>
                                <span title="{{ $file->original_name }}">
                                    {{ Str::limit($file->original_name, 35) }}
                                </span>
                                @if($file->notes)
                                    <br>
                                    <small class="text-muted">{{ $file->notes }}</small>
                                @endif
                            </td>
                            <td class="text-muted">{{ $file->file_size_human }}</td>
                            <td>
                                {{-- #5 Color sebagai label panel saja --}}
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:16px;height:16px;border-radius:50%;
                                                background:{{ $file->layer_color }};
                                                border:2px solid rgba(0,0,0,.1);
                                                flex-shrink:0"></div>
                                    <input type="color"
                                           class="form-control form-control-color p-0 border-0"
                                           value="{{ $file->layer_color }}"
                                           data-file-id="{{ $file->id }}"
                                           data-url="{{ route('projects.files.color', [$project, $file]) }}"
                                           title="Warna penanda di panel layer"
                                           style="width:24px;height:24px;cursor:pointer;
                                                  background:none">
                                    <small class="text-muted" style="font-size:.7rem">
                                        Panel saja
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input layer-toggle"
                                           type="checkbox"
                                           {{ $file->is_visible ? 'checked' : '' }}
                                           data-file-id="{{ $file->id }}"
                                           data-url="{{ route('projects.files.toggle', [$project, $file]) }}"
                                           title="Toggle visibilitas di peta">
                                </div>
                            </td>
                            <td class="pe-3 text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('projects.map', $project) }}"
                                       class="btn btn-sm btn-outline-success"
                                       title="Lihat di peta">
                                        <i class="bi bi-map"></i>
                                    </a>
                                    <a href="{{ $file->public_url }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @if(Auth::user()->canDelete())
                                    <form method="POST"
                                          action="{{ route('projects.files.destroy', [$project, $file]) }}"
                                          onsubmit="return confirm('Hapus file ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
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
            @if($project->kmlFiles->count() > 0)
            <div class="px-3 py-2 border-top bg-light-subtle">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Warna panel hanya untuk penanda di daftar layer.
                    Warna tampilan di peta mengikuti style dalam file KML.
                </small>
            </div>
            @endif
            @endif
        </div>
    </div>

    {{-- ── DOKUMEN PROYEK ───────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-file-earmark-text text-primary me-2"></i>
                Dokumen Proyek
                <span class="badge bg-secondary ms-1">
                    {{ $project->files->where('file_type','document')->count() }}
                </span>
            </h6>
            <button class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                <i class="bi bi-plus-lg me-1"></i>Upload Dokumen
            </button>
        </div>
        <div class="card-body p-0">
            @php
                $documents = $project->files->where('file_type','document');
            @endphp
            @if($documents->isEmpty())
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-file-earmark fs-2 d-block mb-2 opacity-25"></i>
                    Belum ada dokumen. Upload PDF, Word, atau Excel.
                </div>
            @else
            <div class="list-group list-group-flush">
                @foreach($documents as $doc)
                @php
                    $ext = pathinfo($doc->original_name, PATHINFO_EXTENSION);
                    $iconMap = [
                        'pdf'  => ['bi-file-earmark-pdf',  'text-danger'],
                        'doc'  => ['bi-file-earmark-word', 'text-primary'],
                        'docx' => ['bi-file-earmark-word', 'text-primary'],
                        'xls'  => ['bi-file-earmark-excel','text-success'],
                        'xlsx' => ['bi-file-earmark-excel','text-success'],
                        'txt'  => ['bi-file-earmark-text', 'text-secondary'],
                    ];
                    $icon = $iconMap[strtolower($ext)] ?? ['bi-file-earmark','text-secondary'];
                @endphp
                <div class="list-group-item px-3 py-2">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi {{ $icon[0] }} {{ $icon[1] }} fs-4 flex-shrink-0"></i>
                        <div style="flex:1;min-width:0">
                            <div class="small fw-medium text-truncate">
                                {{ $doc->original_name }}
                            </div>
                            <div class="text-muted" style="font-size:.72rem">
                                {{ strtoupper($ext) }} · {{ $doc->file_size_human }}
                                @if($doc->notes)
                                    · {{ $doc->notes }}
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            @if(strtolower(pathinfo($doc->original_name, PATHINFO_EXTENSION)) === 'pdf')
                            <a href="{{ route('projects.files.view', [$project, $doc]) }}"
                                class="btn btn-sm btn-outline-primary"
                                title="Lihat PDF">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endif
                            <a href="{{ $doc->public_url }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-secondary"
                               title="Download/Buka">
                                <i class="bi bi-download"></i>
                            </a>
                            <form method="POST"
                                  action="{{ route('projects.files.destroy', [$project, $doc]) }}"
                                  onsubmit="return confirm('Hapus dokumen ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ── FOTO PROYEK ──────────────────────────────── --}}
    <div class="card">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-images text-warning me-2"></i>
                Foto Proyek
                <span class="badge bg-secondary ms-1">
                    {{ $project->photos->count() }}
                </span>
            </h6>
            <button class="btn btn-sm btn-warning text-dark"
                    data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                <i class="bi bi-plus-lg me-1"></i>Upload Foto
            </button>
        </div>
        <div class="card-body">
            @if($project->photos->isEmpty())
                <div class="text-center py-3 text-muted small">
                    <i class="bi bi-images fs-2 d-block mb-2 opacity-25"></i>
                    Belum ada foto proyek.
                </div>
            @else
            <div class="row g-2">
                @foreach($project->photos as $photo)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="position-relative"
                         style="aspect-ratio:1;overflow:hidden;border-radius:8px">
                        <img src="{{ $photo->public_url }}"
                             alt="{{ $photo->caption ?? 'Foto proyek' }}"
                             class="w-100 h-100"
                             style="object-fit:cover;cursor:pointer"
                             onclick="openLightbox('{{ $photo->public_url }}',
                                                   '{{ $photo->caption }}')">
                        <div class="position-absolute top-0 end-0 p-1">
                            <form method="POST"
                                  action="{{ route('projects.photos.destroy', [$project, $photo]) }}"
                                  onsubmit="return confirm('Hapus foto ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger rounded-circle"
                                        style="width:24px;height:24px;padding:0;
                                               font-size:.7rem">
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

{{-- Modal Upload KML --}}
<div class="modal fade" id="uploadKmlModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-globe me-2 text-success"></i>Upload KML Layer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST"
                  action="{{ route('projects.files.store', $project) }}"
                  enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="file_type" value="kml">
                <div class="modal-body">

                    {{-- Info box --}}
                    <div class="alert alert-success py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Hanya file <strong>.kml</strong> yang diterima.
                        File akan langsung tampil di Google Maps.
                    </div>

                    {{-- Drop Zone --}}
                    <div id="kmlDropZone"
                         class="text-center p-4 mb-3 rounded-3"
                         style="border:2px dashed #198754;cursor:pointer;
                                background:#f8fff8;transition:.2s"
                         onclick="document.getElementById('kmlFileInput').click()">
                        <i class="bi bi-globe fs-2 text-success opacity-50"></i>
                        <p class="mb-0 small text-muted mt-2">
                            Klik atau drag file <strong>.kml</strong> ke sini
                        </p>
                    </div>
                    <input type="file" id="kmlFileInput" name="file"
                           class="d-none" accept=".kml">
                    <div id="kmlFileName" class="small text-success mb-3 d-none">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        <span></span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium small">
                                Warna Penanda di Panel Layer
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" name="layer_color"
                                       value="#FF0000"
                                       class="form-control form-control-color"
                                       style="width:40px;height:32px">
                                <small class="text-muted">
                                    Hanya sebagai penanda warna di daftar layer,
                                    bukan warna di peta
                                </small>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium small">
                                Keterangan (opsional)
                            </label>
                            <input type="text" name="notes"
                                   class="form-control form-control-sm"
                                   placeholder="cth: Jaringan saluran utama 2024">
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm"
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm"
                            id="kmlUploadBtn" disabled>
                        <i class="bi bi-upload me-1"></i>Upload KML
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Upload Dokumen --}}
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                    Upload Dokumen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST"
                  action="{{ route('projects.files.store', $project) }}"
                  enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="file_type" value="document">
                <div class="modal-body">

                    <div class="alert alert-primary py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Format yang diterima:
                        <strong>PDF, DOC, DOCX, XLS, XLSX, TXT</strong>
                        (maks. 50MB)
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small">
                            Pilih Dokumen <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file"
                               class="form-control form-control-sm"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.txt"
                               required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium small">
                            Keterangan (opsional)
                        </label>
                        <input type="text" name="notes"
                               class="form-control form-control-sm"
                               placeholder="cth: Laporan perencanaan teknis">
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm"
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-upload me-1"></i>Upload Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Upload Foto --}}
<div class="modal fade" id="uploadPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold">
                    <i class="bi bi-camera me-2 text-warning"></i>Upload Foto
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
                            Pilih Foto <span class="text-danger">*</span>
                            <span class="text-muted">(maks. 10 foto, 5MB per foto)</span>
                        </label>
                        <input type="file" name="photos[]"
                               class="form-control form-control-sm"
                               multiple accept="image/jpeg,image/png,image/webp"
                               id="photoInput">
                    </div>
                    <div id="photoPreview" class="d-flex gap-2 flex-wrap mb-3"></div>
                    <div>
                        <label class="form-label fw-medium small">
                            Keterangan (opsional)
                        </label>
                        <input type="text" name="caption"
                               class="form-control form-control-sm"
                               placeholder="cth: Kondisi saluran primer, Jan 2024">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm"
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark btn-sm">
                        <i class="bi bi-upload me-1"></i>Upload Foto
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
                <button type="button"
                        class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                        data-bs-dismiss="modal"></button>
                <img id="lightboxImg" src="" alt=""
                     class="img-fluid rounded" style="max-height:80vh">
                <p id="lightboxCaption"
                   class="text-white-50 small mt-2 mb-0"></p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── KML Drop Zone ─────────────────────────────────────
const kmlInput    = document.getElementById('kmlFileInput');
const kmlDropZone = document.getElementById('kmlDropZone');
const kmlFileName = document.getElementById('kmlFileName');
const kmlUploadBtn = document.getElementById('kmlUploadBtn');

function handleKmlFile(file) {
    if (!file) return;

    // Validasi ekstensi di frontend
    const ext = file.name.split('.').pop().toLowerCase();
    if (ext !== 'kml') {
        alert('Hanya file .kml yang diterima!');
        return;
    }

    kmlFileName.querySelector('span').textContent = file.name;
    kmlFileName.classList.remove('d-none');
    kmlDropZone.style.borderColor = '#198754';
    kmlDropZone.style.background  = '#f0fff4';
    kmlUploadBtn.disabled = false;
}

kmlInput?.addEventListener('change', () => {
    if (kmlInput.files[0]) handleKmlFile(kmlInput.files[0]);
});

kmlDropZone?.addEventListener('dragover', e => {
    e.preventDefault();
    kmlDropZone.style.background = '#e8f5e9';
});

kmlDropZone?.addEventListener('dragleave', () => {
    kmlDropZone.style.background = '#f8fff8';
});

kmlDropZone?.addEventListener('drop', e => {
    e.preventDefault();
    kmlDropZone.style.background = '#f8fff8';
    const file = e.dataTransfer.files[0];
    if (file) {
        // Inject ke input
        const dt = new DataTransfer();
        dt.items.add(file);
        kmlInput.files = dt.files;
        handleKmlFile(file);
    }
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
            img.style.cssText =
                'width:60px;height:60px;object-fit:cover;border-radius:6px';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});

// ── Layer Toggle AJAX ─────────────────────────────────
document.querySelectorAll('.layer-toggle').forEach(toggle => {
    toggle.addEventListener('change', function () {
        fetch(this.dataset.url, {
            method:  'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':       'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            const row = this.closest('tr');
            if (row) row.style.opacity = data.is_visible ? '1' : '0.5';
        });
    });
});

// ── Layer Color AJAX ──────────────────────────────────
document.querySelectorAll('.layer-color').forEach(picker => {
    picker.addEventListener('change', function () {
        fetch(this.dataset.url, {
            method:  'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept':       'application/json',
            },
            body: JSON.stringify({ color: this.value })
        });
        // Update dot preview
        this.previousElementSibling.style.background = this.value;
    });
});

// ── Lightbox ──────────────────────────────────────────
function openLightbox(src, caption) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightboxCaption').textContent = caption ?? '';
    new bootstrap.Modal(document.getElementById('lightboxModal')).show();
}
</script>
@endpush