@extends('layouts.app')

@section('title', 'Tambah User')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('users.index') }}">Manajemen User</a>
    </li>
    <li class="breadcrumb-item active">Tambah User</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-6">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0">Tambah User Baru</h4>
            <p class="text-muted small mb-0">Buat akun untuk anggota tim</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-medium">
                        Nama Lengkap <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name"
                           value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="cth. Budi Santoso">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">
                        Email <span class="text-danger">*</span>
                    </label>
                    <input type="email" name="email"
                           value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="budi@epaksi.com">
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
                            id="roleSelect"
                            onchange="updateRoleInfo(this.value)">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin"  {{ old('role')==='admin'  ? 'selected':'' }}>
                            Administrator
                        </option>
                        <option value="editor" {{ old('role')==='editor' ? 'selected':'' }}>
                            Editor
                        </option>
                        <option value="viewer" {{ old('role')==='viewer' ? 'selected':'' }}>
                            Viewer
                        </option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    {{-- Role info box --}}
                    <div id="roleInfo" class="alert py-2 mt-2 small d-none">
                        <i class="bi bi-info-circle me-1"></i>
                        <span id="roleInfoText"></span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium">
                        Password <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Minimal 8 karakter">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        User dapat mengubah password setelah login.
                    </small>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('users.index') }}"
                       class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Buat User
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

  const roleColors = {
      admin: 'danger', editor: 'warning', viewer: 'secondary'
  };

  function updateRoleInfo(role) {
      const box  = document.getElementById('roleInfo');
      const text = document.getElementById('roleInfoText');

      if (role && roleDescriptions[role]) {
          box.className = `alert alert-${roleColors[role]}-subtle py-2 mt-2 small`;
          text.textContent = roleDescriptions[role];
          box.classList.remove('d-none');
      } else {
          box.classList.add('d-none');
      }
  }

  // Trigger jika ada old value
  const sel = document.getElementById('roleSelect');
if (sel.value) updateRoleInfo(sel.value);
</script>
@endpush