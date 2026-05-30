@extends('layouts.main')

@section('content')

<div id="ers-layout-root" data-title="My Profile" data-active="profile">
  <div class="row g-3">
    <div class="col-12">
      <div class="ers-card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Profile Information</span>
          <button class="btn btn-soft btn-sm" onclick="toggleEdit()"><i class="bi bi-pencil"></i> <span id="editLabel">Edit Profile</span></button>
        </div>
        <div class="card-body">
          <div class="text-center mb-4">
            <img id="avatarPreview" class="profile-avatar mb-3" src="{{ $user->profile_picture ? asset($user->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=200&background=2563eb&color=fff' }}" alt="Avatar">
            <h5 class="mb-1" id="profileName">{{ $user->name }}</h5>
            <p class="text-muted small mb-3" id="profileRole">{{ $user->position ?: 'User' }}</p>
          </div>
          <form id="profileForm">
            @csrf
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Full Name</label><input id="pName" name="name" class="form-control" value="{{ $user->name }}" disabled required></div>
              <div class="col-md-6"><label class="form-label">Email</label><input id="pEmail" name="email" type="email" class="form-control" value="{{ $user->email }}" disabled required></div>
              <div class="col-md-6"><label class="form-label">Position</label><input id="pPosition" name="position" class="form-control" value="{{ $user->position }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Department</label><input id="pDepartment" name="department" class="form-control" value="{{ $user->department }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Gender</label>
                <select id="pGender" name="gender" class="form-select" disabled>
                  <option value="">Select gender</option>
                  <option value="Male" {{ $user->gender === 'Male' ? 'selected' : '' }}>Male</option>
                  <option value="Female" {{ $user->gender === 'Female' ? 'selected' : '' }}>Female</option>
                  <option value="Other" {{ $user->gender === 'Other' ? 'selected' : '' }}>Other</option>
                </select>
              </div>
              <div class="col-md-6"><label class="form-label">Salary</label><input id="pSalary" name="salary" type="number" step="0.01" class="form-control" value="{{ $user->salary }}" disabled></div>
              <div class="col-md-6"><label class="form-label">Profile Picture</label><input id="pPicture" name="profile_picture" type="file" class="form-control" accept="image/*" disabled onchange="ersPreviewImage(this,'avatarPreview')"></div>
            </div>
            <div class="text-end mt-3">
              <button id="saveBtn" type="button" class="btn btn-primary d-none" onclick="saveProfile()">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
let editing=false;
function toggleEdit(){
  editing=!editing;
  document.querySelectorAll('#profileForm input, #profileForm select, #profileForm textarea').forEach(el=>el.disabled=!editing);
  document.getElementById('editLabel').textContent = editing?'Cancel':'Edit Profile';
  document.getElementById('saveBtn').classList.toggle('d-none', !editing);
}

async function saveProfile(){
  const form = document.getElementById('profileForm');
  if (!form.checkValidity()) {
    form.classList.add('was-validated');
    return;
  }

  const formData = new FormData();
  formData.append('name', document.getElementById('pName').value.trim());
  formData.append('email', document.getElementById('pEmail').value.trim());
  formData.append('position', document.getElementById('pPosition').value.trim());
  formData.append('department', document.getElementById('pDepartment').value.trim());
  formData.append('gender', document.getElementById('pGender').value);
  formData.append('salary', document.getElementById('pSalary').value ? parseFloat(document.getElementById('pSalary').value) : '');

  const pictureInput = document.getElementById('pPicture');
  if (pictureInput.files.length) {
    formData.append('profile_picture', pictureInput.files[0]);
  }

  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  formData.append('_method', 'PUT');

  const response = await fetch('/api/profile', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': token,
      'Accept': 'application/json'
    },
    body: formData
  });

  const result = await response.json();
  if (result.success) {
    document.getElementById('profileName').textContent = result.user.name;
    document.getElementById('profileRole').textContent = result.user.position || 'User';
    ersToast('success', result.message);
    toggleEdit();
  } else {
    const message = result.errors ? Object.values(result.errors).flat()[0] : 'Unable to update profile.';
    Swal.fire({ icon:'error', title:'Error', text: message });
  }
}
</script>
@endpush