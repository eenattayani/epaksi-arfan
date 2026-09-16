@extends('layouts.app')

@section('title', 'Edit Project')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('projects.index') }}">Projects</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('projects.show', $project) }}">{{ $project->code }}</a>
    </li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('projects.show', $project) }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0">Edit Project</h4>
            <p class="text-muted small mb-0 font-monospace">{{ $project->code }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('projects.update', $project) }}">
        @csrf @method('PUT')

        {{-- Basic Info --}}
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle text-primary me-2"></i>Basic Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-medium">Project Name <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               value="{{ old('name', $project->name) }}"
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Project Code <span class="text-danger">*</span></label>
                        <input type="text" name="code"
                               value="{{ old('code', $project->code) }}"
                               class="form-control font-monospace @error('code') is-invalid @enderror">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Description</label>
                        <textarea name="description" rows="3"
                                  class="form-control">{{ old('description', $project->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Location --}}
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-geo-alt text-danger me-2"></i>Location
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-medium">Location / Village</label>
                        <input type="text" name="location"
                               value="{{ old('location', $project->location) }}"
                               class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">District (Kabupaten)</label>
                        <input type="text" name="district"
                               value="{{ old('district', $project->district) }}"
                               class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Province</label>
                        <input type="text" name="province"
                               value="{{ old('province', $project->province) }}"
                               class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Area (Hectare)</label>
                        <div class="input-group">
                            <input type="number" name="area_hectare"
                                   value="{{ old('area_hectare', $project->area_hectare) }}"
                                   class="form-control" step="0.01" min="0">
                            <span class="input-group-text">Ha</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline & Status --}}
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar-range text-success me-2"></i>Timeline & Status
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select">
                            @foreach(['planned'=>'Planned','active'=>'Active',
                                      'completed'=>'Completed','suspended'=>'Suspended'] as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('status', $project->status) === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Start Date</label>
                        <input type="date" name="start_date"
                               value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
                               class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">End Date</label>
                        <input type="date" name="end_date"
                               value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}"
                               class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('projects.show', $project) }}"
               class="btn btn-outline-secondary px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Update Project
            </button>
        </div>
    </form>

</div>
</div>
@endsection