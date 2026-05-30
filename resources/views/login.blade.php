@extends('layouts.main')

@section('content')

<div class="auth-page">
  <div class="auth-card">
    <div class="brand"><i class="bi bi-grid-1x2-fill"></i> Employee Records</div>
    <h4>Welcome back</h4>
    <form id="loginForm" novalidate>
      @csrf
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input id="email" name="email" type="email" class="form-control form-control-lg" placeholder="you@company.com" required>
        <div class="invalid-feedback">Please enter a valid email.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input id="password" name="password" type="password" class="form-control form-control-lg" placeholder="••••••••" required>
        <div class="invalid-feedback">Password is required.</div>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="remember" name="remember">
          <label class="form-check-label small" for="remember">Remember me</label>
        </div>
        <a href="#" class="small text-decoration-none">Forgot password?</a>
      </div>
      <button class="btn btn-primary btn-lg w-100" type="submit">Sign in</button>
      <p class="text-center small mt-3 mb-0 text-muted">
        Don't have an account? <a href="register" class="text-decoration-none fw-semibold">Register</a>
      </p>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', function(e){
  e.preventDefault();
  if (!this.checkValidity()) {
    this.classList.add('was-validated');
    Swal.fire({icon:'error', title:'Invalid login', text:'Please check your credentials and try again.'});
    return;
  }

  const formData = new FormData(this);
  const data = {
    email: formData.get('email'),
    password: formData.get('password'),
    remember: formData.get('remember') === 'on'
  };

  fetch('/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      ersToast('success', result.message);
      setTimeout(() => location.href = '/dashboard', 900);
    } else {
      const message = result.errors ? Object.values(result.errors).flat()[0] : 'Login failed. Please try again.';
      Swal.fire({icon:'error', title:'Login failed', text: message});
    }
  })
  .catch(error => {
    console.error('Login error:', error);
    Swal.fire({icon:'error', title:'Login failed', text:'An unexpected error occurred.'});
  });
});
</script>
@endpush