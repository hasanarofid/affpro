@php
    $settings = app(\App\Services\SettingService::class);
    $primaryColor = $settings->primaryColor();
    $secondaryColor = $settings->secondaryColor();
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — {{ $settings->storeName() }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg,
                    {{ $primaryColor }}
                    0%,
                    {{ $secondaryColor }}
                    50%, #0f172a 100%);
        }

        .register-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
        }

        .form-control {
            border-radius: 10px;
            padding: .65rem 1rem;
        }

        .form-control:focus {
            border-color:
                {{ $primaryColor }}
            ;
            box-shadow: 0 0 0 3px
                {{ $primaryColor }}
                33;
        }

        .btn-register {
            background: linear-gradient(135deg,
                    {{ $primaryColor }}
                    ,
                    {{ $secondaryColor }}
                );
            border: none;
            border-radius: 12px;
            padding: .7rem;
            font-weight: 600;
            font-size: .95rem;
            transition: transform .15s, box-shadow .15s;
        }

        .btn-register:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px
                {{ $primaryColor }}
                55;
        }
    </style>
</head>

<body>
    <div class="register-card">
        <div class="text-center mb-4">
            <h4 class="fw-bold" style="color:{{ $primaryColor }}">
                <i class="bi bi-shop"></i> {{ $settings->storeName() }}
            </h4>
            <p class="text-muted small mb-0">Buat akun untuk mulai berbelanja</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 small" style="border-radius:10px">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-medium">Nama Lengkap</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus
                    placeholder="Nama lengkap Anda">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required
                    placeholder="email@contoh.com">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium">No. Telepon <span class="text-muted">(opsional)</span></label>
                <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}"
                    inputmode="numeric" autocomplete="tel" maxlength="15"
                    oninput="this.value = this.value.replace(/[^0-9+]/g, '').slice(0, 15)"
                    placeholder="08xxxxxxxxxx">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Kata Sandi</label>
                <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-medium">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" class="form-control" required
                    placeholder="Ulangi kata sandi">
            </div>
            <button type="submit" class="btn btn-register btn-primary w-100">
                <i class="bi bi-person-plus me-1"></i> Daftar
            </button>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">Sudah punya akun? <a href="{{ route('login') }}"
                    style="color:{{ $primaryColor }};text-decoration:none;font-weight:600">Masuk</a></small>
        </div>
    </div>
</body>

</html>