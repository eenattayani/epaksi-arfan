@extends('layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('users.index') }}">Manajemen User</a>
    </li>
    <li class="breadcrumb-item active">Edit {{ $user->name }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-6">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0">Edit User</h4>
            <p class="text-muted small mb-0">{{ $user->email }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-medium">
                        Nama Lengkap <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name"
                           value="{{ old('name', $user->name) }}"
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">
                        Email <span class="text-danger">*</span>
                    </label>
                    <input type="email" name="email"
                           value="{{ old('email', $user->email) }}"
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">
                        Role <span class="text-danger">*</span>
                    </label>
                    <select name="role"
                            class="form-select @error('role') is-invalid @enderror"
                            {{ $user->id === auth()->id() ? 'disabled' : '' }}
                            onchange="updateRoleInfo(this.value)">
                        @foreach(['admin'=>'Administrator','editor'=>'Editor','viewer'=>'Viewer']
                                  as $val => $label)
                        <option value="{{ $val }}"
                            {{ old('role', $user->role) === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @if($user->id === auth()->id())
                        {{-- Kirim value via hidden input karena select disabled --}}
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <small class="text-muted">
                            <i class="bi bi-lock me-1"></i>
                            Kamu tidak bisa mengubah role dirimu sendiri.
                        </small>
                    @endif
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div id="roleInfo" class="alert py-2 mt-2 small d-none"></div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium">Password Baru</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Kosongkan jika tidak ingin mengubah password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        Minimal 8 karakter. Biarkan kosong jika tidak ingin diubah.
                    </small>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('users.index') }}"
                       class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
const roleDescriptions = {
    admin:  'Akses penuh ke semua fitur termasuk kelola user dan hapus project.',
    editor: 'Dapat membuat, edit project, dan upload file. Tidak dapat menghapus project.',
    viewer: 'Hanya dapat melihat project dan membuka peta. Tidak dapat mengubah data.',
};
const roleColors = { admin: 'danger', editor: 'warning', viewer: 'secondary' };

function updateRoleInfo(role) {
    const box = document.getElementById('roleInfo');
    if (role && roleDescriptions[role]) {
        box.className = `alert alert-${roleColors[role]}-subtle py-2 mt-2 small`;
        box.innerHTML = `<i class="bi bi-info-circle me-1"></i>${roleDescriptions[role]}`;
        box.classList.remove('d-none');
    } else {
        box.classList.add('d-none');
    }
}
</script>
@endpush