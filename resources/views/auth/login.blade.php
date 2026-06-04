<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — MGVCL Feeder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f0f4f8; }
        .login-card { max-width: 400px; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,.1); }
        .brand-header { background: #1a3a5c; border-radius: 12px 12px 0 0; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
<div class="login-card card border-0 w-100 mx-3">
    <div class="brand-header text-white text-center py-4 px-3">
        <i class="bi bi-lightning-charge-fill fs-1"></i>
        <h4 class="mb-0 mt-2 fw-bold">MGVCL Feeder</h4>
        <small class="opacity-75">Power Position Management</small>
    </div>
    <div class="card-body p-4">
        @if($errors->any())
            <div class="alert alert-danger py-2">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('status'))
            <div class="alert alert-success py-2">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="employee_id" class="form-label fw-semibold">Employee ID</label>
                <input type="text"
                       id="employee_id"
                       name="employee_id"
                       class="form-control form-control-lg @error('employee_id') is-invalid @enderror"
                       value="{{ old('employee_id') }}"
                       placeholder="Enter your employee ID"
                       autocomplete="username"
                       autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control form-control-lg"
                       placeholder="Enter your password"
                       autocomplete="current-password">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-lg">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
        </form>
    </div>
    <div class="card-footer text-center text-muted small py-2">
        MGVCL &copy; {{ date('Y') }} — Internal Use Only
    </div>
</div>
</body>
</html>
