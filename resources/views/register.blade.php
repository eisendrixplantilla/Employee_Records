@extends('layouts.main')

@section('content')

<div class="auth-page">
  <div class="auth-card" style="max-width: 900px;">
    <div class="brand"><i class="bi bi-grid-1x2-fill"></i> Employee Records</div>
    <h4>Create your account</h4>
    <p class="text-center text-muted small mb-4">Get started in less than a minute</p>
    <form id="registerForm" novalidate>
      @csrf
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control form-control-lg" placeholder="John Doe" required>
        <div class="invalid-feedback">Name is required.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control form-control-lg" placeholder="you@company.com" required>
        <div class="invalid-feedback">Please enter a valid email.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Position</label>
        <input type="text" name="position" class="form-control form-control-lg" placeholder="Job Title" required>
        <div class="invalid-feedback">Position is required.</div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Department</label>
          <select name="department" class="form-control form-control-lg" required>
            <option value="">Select Department</option>
            <option value="HR">HR</option>
            <option value="IT">IT</option>
            <option value="Finance">Finance</option>
            <option value="Sales">Sales</option>
            <option value="Marketing">Marketing</option>
            <option value="Operations">Operations</option>
          </select>
          <div class="invalid-feedback">Please select a department.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Gender</label>
          <select name="gender" class="form-control form-control-lg" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
          </select>
          <div class="invalid-feedback">Please select a gender.</div>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Salary</label>
        <input type="number" name="salary" class="form-control form-control-lg" placeholder="0.00" step="0.01" required>
        <div class="invalid-feedback">Salary is required.</div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Password</label>
          <input id="pw" name="password" type="password" class="form-control form-control-lg" required minlength="6">
          <div class="invalid-feedback">Min 6 characters.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Confirm Password</label>
          <input id="pw2" name="password_confirmation" type="password" class="form-control form-control-lg" required>
          <div class="invalid-feedback">Passwords must match.</div>
        </div>
      </div>
      <button class="btn btn-primary btn-lg w-100" type="submit">Create Account</button>
      <p class="text-center small mt-3 mb-0 text-muted">
        Already have an account? <a href="login" class="text-decoration-none fw-semibold">Sign in</a>
      </p>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('registerForm').addEventListener('submit', function(e){
  e.preventDefault();
  const pw = document.getElementById('pw').value;
  const pw2 = document.getElementById('pw2').value;
  if (pw !== pw2) document.getElementById('pw2').setCustomValidity('mismatch');
  else document.getElementById('pw2').setCustomValidity('');
  if (!this.checkValidity()) { this.classList.add('was-validated'); return; }
  
  const formData = new FormData(this);
  const data = Object.fromEntries(formData);
  
  fetch('/api/users', {
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
      setTimeout(() => location.href = '/login', 1500);
    } else {
      if (result.errors) {
        Object.keys(result.errors).forEach(field => {
          const msg = result.errors[field][0];
          ersToast('error', msg);
        });
      } else {
        ersToast('error', 'Registration failed. Please try again.');
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    ersToast('error', 'An error occurred. Please try again.');
  });
});
</script>
@endpush