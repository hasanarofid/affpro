<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php $settings = app(\App\Services\SettingService::class); @endphp
    <title>{{ __('auth.login') }} — {{ $settings->storeName() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg,
                    {{ $settings->primaryColor() }}
                    0%,
                    {{ $settings->secondaryColor() }}
                    50%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, .97);
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, .3);
        }

        .brand-logo {
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg,
                    {{ $settings->primaryColor() }}
                    ,
                    {{ $settings->secondaryColor() }}
                );
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
        }

        .form-control:focus {
            border-color:
                {{ $settings->primaryColor() }}
            ;
            box-shadow: 0 0 0 3px
                {{ $settings->primaryColor() }}
                22;
        }

        .btn-login {
            background: linear-gradient(135deg,
                    {{ $settings->primaryColor() }}
                    ,
                    {{ $settings->secondaryColor() }}
                );
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            color: #fff;
            transition: all .3s;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px
                {{ $settings->primaryColor() }}
                55;
            color: #fff;
        }

        .form-label {
            font-weight: 500;
            font-size: .875rem;
            color: #374151;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="brand-logo mb-2"><i class="bi bi-shop me-2"></i>{{ $settings->storeName() }}</div>
            <p class="text-muted small mb-0">Masuk ke akun Anda</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 px-3" style="font-size:.85rem; border-radius:10px;">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ __('auth.email') }}</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus
                    placeholder="email@contoh.com">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('auth.password') }}</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <div class="d-flex align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label small" for="remember">{{ __('auth.remember_me') }}</label>
                </div>
            </div>
            <button type="submit" class="btn btn-login w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>{{ __('auth.login') }}
            </button>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">Belum punya akun? <a href="{{ route('register') }}"
                    style="color:{{ $settings->primaryColor() }};text-decoration:none;font-weight:600">Daftar</a></small>
        </div>
        <div class="text-center mt-2">
            <a href="{{ route('otp.login') }}" class="small" style="color:{{ $settings->primaryColor() }};text-decoration:none">
                <i class="bi bi-whatsapp me-1"></i>Login via WhatsApp OTP
            </a>
        </div>
    </div>
</body>

</html>