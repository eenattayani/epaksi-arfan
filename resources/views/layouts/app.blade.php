<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EPAKSI GIS') — Geoportal Engineering</title>

    <meta name="developer" content="Enry Firmana - enryfirmana@gmail.com - https://ellokale.com" />

    <link rel="shortcut icon" href="logo.png" type="image/x-icon" />

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* ── Layout ───────────────────────────────── */
        body { background: #f0f4f8; font-family: 'Segoe UI', sans-serif; }

        #sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, #1a3c5e 0%, #0f2540 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        #sidebar .brand {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        #sidebar .brand h5 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1.1rem;
        }

        #sidebar .brand small { color: #90b4d4; font-size: 0.75rem; }

        #sidebar .nav-link {
            color: #b8d0e8;
            padding: 0.65rem 1.25rem;
            border-radius: 8px;
            margin: 2px 0.75rem;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        #sidebar .nav-link i { width: 20px; margin-right: 8px; }

        #main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-content { padding: 1.5rem; }

        /* ── Cards ────────────────────────────────── */
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); border-radius: 12px; }
        .card-header { border-radius: 12px 12px 0 0 !important; }

        /* ── Status badges ────────────────────────── */
        .badge-status { font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; }

        /* ── Stat cards ───────────────────────────── */
        .stat-card {
            border-radius: 12px;
            padding: 1.25rem;
            color: #fff;
            border: none;
        }

        /* ── Responsive ───────────────────────────── */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ── SIDEBAR ──────────────────────────────────── --}}
<div id="sidebar">
    <div class="brand">
        <div class="d-flex align-items-center gap-2">
            <div style="width:36px;height:36px;background:rgba(255,255,255,0.15);
                        border-radius:8px;display:flex;align-items:center;
                        justify-content:center;">
                <i class="bi bi-water text-white"></i>
            </div>
            <div>
                <h5>GE GIS</h5>
                <small>Irrigation Project Management</small>
            </div>
        </div>
    </div>

    <nav class="nav flex-column pt-2">
				<span class="text-uppercase px-3 py-2"
							style="color:#5a8ab0;font-size:.65rem;letter-spacing:.08em">
						Main Menu
				</span>

				<a href="{{ route('dashboard') }}"
					class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
						<i class="bi bi-speedometer2"></i> Dashboard
				</a>

				<a href="{{ route('projects.index') }}"
					class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
						<i class="bi bi-folder2-open"></i> Projects
				</a>

				<a href="{{ route('map.explorer') }}"
					class="nav-link {{ request()->routeIs('map.explorer') ? 'active' : '' }}">
						<i class="bi bi-map"></i> Map Explorer
				</a>

				<hr style="border-color:rgba(255,255,255,.1);margin:.5rem 1rem">

				<span class="text-uppercase px-3 py-2"
							style="color:#5a8ab0;font-size:.65rem;letter-spacing:.08em">
						System
				</span>

				<a href="#" class="nav-link">
						<i class="bi bi-gear"></i> Settings
				</a>
		</nav>
</div>

{{-- ── MAIN CONTENT ─────────────────────────────── --}}
<div id="main-content">

    {{-- Topbar --}}
    <div class="topbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-md-none" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <nav aria-label="breadcrumb" class="mb-0">
                <ol class="breadcrumb mb-0 small">
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                <i class="bi bi-circle-fill" style="font-size:.5rem"></i> System Online
            </span>
        </div>
    </div>

    {{-- Flash Messages --}}
    <div class="px-4 pt-3">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
    </div>

    {{-- Page Content --}}
    <div class="page-content">
        @yield('content')
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Mobile sidebar toggle
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });
</script>

@stack('scripts')
</body>
</html>