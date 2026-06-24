<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Installer') — Jadiorder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --installer-primary: #4F46E5;
            --installer-primary-dark: #4338CA;
            --installer-gradient: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            --installer-bg: #f0f2f5;
            --installer-surface: #ffffff;
            --installer-text: #1E293B;
            --installer-muted: #64748B;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--installer-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─── Background Pattern ─── */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 30%, rgba(79, 70, 229, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(124, 58, 237, 0.06) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .installer-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        .installer-container {
            width: 100%;
            max-width: 750px;
        }

        /* ─── Brand Header ─── */
        .installer-brand {
            text-align: center;
            margin-bottom: 30px;
        }

        .installer-brand h1 {
            font-size: 2.2rem;
            font-weight: 900;
            background: var(--installer-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .installer-brand p {
            color: var(--installer-muted);
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 4px;
        }

        /* ─── Steps Indicator ─── */
        .installer-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0;
            margin-bottom: 30px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #94A3B8;
            position: relative;
        }

        .step-item .step-num {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #E2E8F0;
            color: #94A3B8;
            font-weight: 700;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .step-item.active .step-num {
            background: var(--installer-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.35);
        }

        .step-item.active {
            color: var(--installer-primary);
        }

        .step-item.completed .step-num {
            background: #10B981;
            color: white;
        }

        .step-item.completed {
            color: #10B981;
        }

        .step-connector {
            width: 50px;
            height: 2px;
            background: #E2E8F0;
            margin: 0 8px;
            border-radius: 1px;
            flex-shrink: 0;
        }

        .step-connector.completed {
            background: #10B981;
        }

        /* ─── Main Card ─── */
        .installer-card {
            background: var(--installer-surface);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .installer-card-header {
            background: var(--installer-gradient);
            padding: 35px 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .installer-card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .installer-card-header h3 {
            font-weight: 800;
            font-size: 1.4rem;
            position: relative;
            z-index: 1;
        }

        .installer-card-header p {
            opacity: 0.85;
            font-size: 0.9rem;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }

        .installer-card-body {
            padding: 35px 40px;
        }

        /* ─── Form Styles ─── */
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--installer-text);
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1.5px solid #E2E8F0;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--installer-primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .input-group-text {
            border-radius: 12px 0 0 12px;
            border: 1.5px solid #E2E8F0;
            background: #F8FAFC;
            border-right: none;
            color: var(--installer-muted);
        }

        .input-group .form-control {
            border-radius: 0 12px 12px 0;
        }

        /* ─── Buttons ─── */
        .btn-installer {
            background: var(--installer-gradient);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 14px 30px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.3px;
        }

        .btn-installer:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.35);
            color: white;
        }

        .btn-installer:disabled {
            opacity: 0.6;
            transform: none;
            box-shadow: none;
        }

        .btn-outline-installer {
            border: 2px solid #E2E8F0;
            color: var(--installer-muted);
            border-radius: 14px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 0.9rem;
            background: transparent;
            transition: all 0.2s ease;
        }

        .btn-outline-installer:hover {
            border-color: var(--installer-primary);
            color: var(--installer-primary);
            background: rgba(79, 70, 229, 0.04);
        }

        /* ─── Alerts ─── */
        .installer-alert {
            border-radius: 14px;
            border: none;
            font-size: 0.88rem;
            padding: 14px 18px;
            font-weight: 500;
        }

        .installer-alert.alert-danger {
            background: #FEF2F2;
            color: #991B1B;
        }

        .installer-alert.alert-success {
            background: #F0FDF4;
            color: #166534;
        }

        .installer-alert.alert-warning {
            background: #FFFBEB;
            color: #92400E;
        }

        /* ─── Footer ─── */
        .installer-footer {
            text-align: center;
            padding: 20px;
            color: var(--installer-muted);
            font-size: 0.78rem;
            font-weight: 500;
        }

        /* ─── Requirement Row ─── */
        .req-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 6px;
            background: #F8FAFC;
            font-size: 0.88rem;
        }

        .req-row .req-name {
            font-weight: 600;
            color: var(--installer-text);
        }

        .req-row .req-current {
            font-size: 0.78rem;
            color: var(--installer-muted);
        }

        /* ─── Progress Bar animation ─── */
        .install-progress-bar {
            height: 8px;
            border-radius: 4px;
            background: #E2E8F0;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .install-progress-bar .bar {
            height: 100%;
            background: var(--installer-gradient);
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        /* ─── Responsive ─── */
        @media (max-width: 600px) {
            .installer-card-body {
                padding: 25px 20px;
            }

            .installer-card-header {
                padding: 25px 20px;
            }

            .step-item .step-label {
                display: none;
            }

            .step-connector {
                width: 30px;
            }
        }
    </style>
    @yield('styles')
</head>

<body>
    <div class="installer-wrapper">
        <div class="installer-container">
            <div class="installer-brand">
                <h1>JadiOrder</h1>
                <p>Web Installer</p>
            </div>

            @yield('steps')

            @if(session('error'))
                <div class="alert installer-alert alert-danger mb-3">
                    <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="alert installer-alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                </div>
            @endif
            @if(session('success'))
                <div class="alert installer-alert alert-success mb-3">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert installer-alert alert-danger mb-3">
                    <i class="bi bi-x-circle me-2"></i>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

            <div class="installer-footer">
                &copy; {{ date('Y') }} DapurKode — JadiOrder Installer v1.0
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>

</html>
