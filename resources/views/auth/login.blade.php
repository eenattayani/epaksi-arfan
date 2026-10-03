<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — EPAKSI GIS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f2540 0%, #1a3c5e 50%, #0d4f8c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #1a3c5e, #0d4f8c);
            padding: 2rem;
            text-align: center;
            color: #fff;
        }

        .login-header .logo-circle {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,.15);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .login-header h4 {
            font-weight: 700;
            margin: 0;
            font-size: 1.3rem;
        }

        .login-header p {
            margin: .25rem 0 0;
            opacity: .75;
            font-size: .85rem;
        }

        .login-body { padding: 2rem; }

        .form-label {
            font-weight: 500;
            font-size: .875rem;
            color: #374151;
        }

        .form-control {
            border-radius: 8px;
            padding: .6rem .9rem;
            border: 1.5px solid #e5e7eb;
            font-size: .9rem;
            transition: border-color .2s;
        }

        .form-control:focus {
            border-color: #1a3c5e;
            box-shadow: 0 0 0 3px rgba(26,60,94,.1);
        }

        .input-group-text {
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px 0 0 8px;
            color: #6b7280;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
            border-left: none;
        }

        .btn-login {
            background: linear-gradient(135deg, #1a3c5e, #0d4f8c);
            border: none;
            border-radius: 8px;
            padding: .7rem;
            font-weight: 600;
            font-size: .95rem;
            letter-spacing: .02em;
            transition: opacity .2s, transform .1s;
        }

        .btn-login:hover {
            opacity: .92;
            transform: translateY(-1px);
        }

        .btn-login:active { transform: translateY(0); }

        .login-footer {
            text-align: center;
            padding: 1rem 2rem 1.5rem;
            color: #9ca3af;
            font-size: .78rem;
            border-top: 1px solid #f3f4f6;
        }

        /* Floating water drop decoration */
        .bg-decoration {
            position: fixed;
            pointer-events: none;
            opacity: .06;
        }

        .bg-decoration.d1 {
            font-size: 20rem;
            bottom: -5rem;
            right: -5rem;
            color: #fff;
        }

        .bg-decoration.d2 {
            font-size: 12rem;
            top: -3rem;
            left: -3rem;
            color: #fff;
        }
    </style>
</head>
<body>

{{-- Background decorations --}}
<i class="bi bi-water bg-decoration d1"></i>
<i class="bi bi-droplet-half bg-decoration d2"></i>

<div class="login-wrapper">

    {{-- Logo & Title --}}
    <div class="text-center mb-4">
        <div style="color:rgba(255,255,255,.9);font-size:2rem;font-weight:800;
                    letter-spacing:-.02em">
            EPAKSI GIS
        </div>
        <div style="color:rgba(255,255,255,.5);font-size:.82rem;margin-top:.25rem">
            Irrigation Project Management System
        </div>
    </div>

    <div class="login-card">

        {{-- Header --}}
        <div class="login-header">
            <div class="logo-circle">
                <i class="bi bi-water fs-3"></i>
            </div>
            <h4>Welcome Back</h4>
            <p>Sign in to access your projects</p>
        </div>

        {{-- Form --}}
        <div class="login-body">

            {{-- Session Error --}}
            @if(session('status'))
                <div class="alert alert-success alert-sm mb-3 py-2 small">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope" style="font-size:.85rem"></i>
                        </span>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="admin@example.com"
                               autofocus
                               required>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">
                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock" style="font-size:.85rem"></i>
                        </span>
                        <input type="password"
                               name="password"
                               id="passwordInput"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="••••••••"
                               required>
                        <button type="button"
                                class="btn btn-outline-secondary"
                                style="border-radius:0 8px 8px 0;border:1.5px solid #e5e7eb;
                                       border-left:none"
                                onclick="togglePassword()">
                            <i class="bi bi-eye" id="eyeIcon" style="font-size:.85rem"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">
                            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Remember Me + Forgot Password --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">
                            Remember me
                        </label>
                    </div>
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="small text-decoration-none"
                       style="color:#1a3c5e">
                        Forgot password?
                    </a>
                    @endif
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-login btn-primary w-100 text-white">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>

            </form>
        </div>

        <div class="login-footer">
            <i class="bi bi-shield-lock me-1"></i>
            Access restricted to authorized personnel only
        </div>

    </div>

    <div class="text-center mt-3" style="color:rgba(255,255,255,.4);font-size:.75rem">
        EPAKSI GIS Geoportal Engineering v1.0 &copy; {{ date('Y') }}
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword() {
    const input   = document.getElementById('passwordInput');
    const icon    = document.getElementById('eyeIcon');
    const isHidden = input.type === 'password';
    input.type    = isHidden ? 'text' : 'password';
    icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
}
</script>

</body>
</html>