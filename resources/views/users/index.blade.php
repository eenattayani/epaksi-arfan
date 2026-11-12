@extends('layouts.app')

@section('title', 'Manajemen User')

@section('breadcrumb')
    <li class="breadcrumb-item active">Manajemen User</li>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Manajemen User</h4>
        <p class="text-muted small mb-0">Kelola akun dan hak akses pengguna sistem</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i> Tambah User
    </a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Total User',  'icon'=>'people',       'color'=>'#1a3c5e',
         'val'=> $users->count()],
        ['label'=>'Admin',       'icon'=>'shield-check',  'color'=>'#dc3545',
         'val'=> $users->where('role','admin')->count()],
        ['label'=>'Editor',      'icon'=>'pencil-square', 'color'=>'#fd7e14',
         'val'=> $users->where('role','editor')->count()],
        ['label'=>'Viewer',      'icon'=>'eye',           'color'=>'#6c757d',
         'val'=> $users->where('role','viewer')->count()],
    ] as $stat)
    <div class="col-6 col-md-3">
        <div class="card border-0 h-100"
             style="background:{{ $stat['color'] }};color:#fff">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-3 fw-bold">{{ $stat['val'] }}</div>
                        <div class="small opacity-75">{{ $stat['label'] }}</div>
                    </div>
                    <i class="bi bi-{{ $stat['icon'] }} fs-3 opacity-40"></i>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- User Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                {{-- Avatar --}}
                                <div style="width:34px;height:34px;border-radius:50%;
                                            background:#e9ecef;display:flex;
                                            align-items:center;justify-content:center;
                                            flex-shrink:0;font-size:.85rem;
                                            font-weight:600;color:#495057">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold small">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                        <span class="badge bg-light text-muted border ms-1"
                                              style="font-size:.6rem">Kamu</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="small text-muted">{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-{{ $user->role_color }}-subtle
                                         text-{{ $user->role_color }}
                                         border border-{{ $user->role_color }}-subtle"
                                  style="font-size:.75rem">
                                {{ $user->role_label }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('users.edit', $user) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST"
                                      action="{{ route('users.destroy', $user) }}"
                                      onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"
                                            title="Hapus">
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
    </div>
</div>

{{-- Role Legend --}}
<div class="card mt-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-info-circle text-primary me-2"></i>Penjelasan Role
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach([
                ['role'=>'Admin','color'=>'danger',
                 'desc'=>'Akses penuh ke semua fitur termasuk kelola user, hapus project, dan semua pengaturan sistem.'],
                ['role'=>'Editor','color'=>'warning',
                 'desc'=>'Dapat membuat project, edit project, upload KML/dokumen/foto. Tidak dapat menghapus project.'],
                ['role'=>'Viewer','color'=>'secondary',
                 'desc'=>'Hanya dapat melihat project, membuka peta, dan membaca dokumen. Tidak dapat mengubah data apapun.'],
            ] as $item)
            <div class="col-md-4">
                <div class="d-flex gap-2 align-items-start">
                    <span class="badge bg-{{ $item['color'] }}-subtle
                                 text-{{ $item['color'] }}
                                 border border-{{ $item['color'] }}-subtle mt-1"
                          style="font-size:.75rem;white-space:nowrap">
                        {{ $item['role'] }}
                    </span>
                    <small class="text-muted">{{ $item['desc'] }}</small>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection