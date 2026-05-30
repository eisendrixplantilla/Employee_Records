@extends('layouts.main')

@section('content')

<div id="ers-layout-root" data-title="Users Management" data-active="users">

  <div class="ers-card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
      <div class="d-flex gap-2 flex-wrap">
        <div class="input-group" style="max-width:280px">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input id="userSearch" class="form-control" placeholder="Search users...">
        </div>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openUserModal()">
        <i class="bi bi-plus-lg"></i> Add User
      </button>
    </div>
    <div class="table-responsive">
      <table class="table mb-0" id="usersTable">
        <thead><tr><th>Name</th><th>Email</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
    <div class="card-body d-flex justify-content-between align-items-center"></div>
  </div>

</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="userModalTitle">Add User</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="userForm" novalidate>
          @csrf
          <input type="hidden" id="uId" name="id">
          <div class="mb-3"><label class="form-label">Full Name</label><input id="uName" name="name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Email</label><input id="uEmail" name="email" type="email" class="form-control" required></div>

          <div class="create-only">
            <div class="mb-3"><label class="form-label">Position</label><input id="uPosition" name="position" class="form-control" placeholder="Job Title"></div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Department</label>
                <select id="uDepartment" name="department" class="form-control">
                  <option value="">Select Department</option>
                  <option value="HR">HR</option>
                  <option value="IT">IT</option>
                  <option value="Finance">Finance</option>
                  <option value="Sales">Sales</option>
                  <option value="Marketing">Marketing</option>
                  <option value="Operations">Operations</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Gender</label>
                <select id="uGender" name="gender" class="form-control">
                  <option value="">Select Gender</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
            <div class="mb-3"><label class="form-label">Salary</label><input id="uSalary" name="salary" type="number" step="0.01" class="form-control" placeholder="0.00"></div>
            <div class="mb-3"><label class="form-label">Password</label><input id="uPassword" name="password" type="password" class="form-control" placeholder="Enter password"></div>
          </div>

          <div class="edit-only d-none">
            <div class="mb-3"><label class="form-label">Current Password</label><input id="uCurrentPassword" name="current_password" type="password" class="form-control" placeholder="Enter current password"></div>
            <div class="mb-3"><label class="form-label">New Password</label><input id="uNewPassword" name="password" type="password" class="form-control" placeholder="Enter new password"></div>
            <div class="mb-3"><label class="form-label">Confirm Password</label><input id="uPasswordConfirmation" name="password_confirmation" type="password" class="form-control" placeholder="Confirm new password"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" onclick="saveUser()">Save</button></div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
let editingMode = false;
let editingUserId = null;

const usersTableBody = document.querySelector('#usersTable tbody');
const userSearch = document.getElementById('userSearch');

function escapeHtml(text) {
  if (!text) return '';
  return text.replace(/[&<>"']/g, tag => ({
    '&':'&amp;',
    '<':'&lt;',
    '>':'&gt;',
    '"':'&quot;',
    "'":'&#39;'
  }[tag]));
}

function renderUsers(users) {
  usersTableBody.innerHTML = users.map(user => `
    <tr data-user-id="${user.id}">
      <td>${escapeHtml(user.name)}</td>
      <td>${escapeHtml(user.email)}</td>
      <td>${new Date(user.created_at).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' })}</td>
      <td class="text-end">
        <button class="btn btn-soft btn-icon me-1" onclick="editUser(${user.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-icon" style="background:#fee2e2;color:#dc2626" onclick="deleteUser(${user.id}, this)"><i class="bi bi-trash"></i></button>
      </td>
    </tr>
  `).join('');
}

async function fetchUsers(query = '') {
  const url = query ? `/api/users?q=${encodeURIComponent(query)}` : '/api/users';
  const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
  const result = await response.json();
  if (result.success) {
    renderUsers(result.users);
  }
}

function openUserModal(id = null, name = '', email = '', position = '', department = '', gender = '', salary = '') {
  editingMode = !!id;
  editingUserId = id;
  document.getElementById('userModalTitle').textContent = editingMode ? 'Edit User' : 'Add User';
  document.getElementById('uId').value = id || '';
  document.getElementById('uName').value = name;
  document.getElementById('uEmail').value = email;
  document.getElementById('uPosition').value = position;
  document.getElementById('uDepartment').value = department;
  document.getElementById('uGender').value = gender;
  document.getElementById('uSalary').value = salary;
  document.getElementById('uPassword').value = '';
  document.getElementById('uCurrentPassword').value = '';
  document.getElementById('uNewPassword').value = '';
  document.getElementById('uPasswordConfirmation').value = '';
  document.getElementById('userForm').classList.remove('was-validated');

  document.querySelectorAll('.create-only').forEach(el => el.classList.toggle('d-none', editingMode));
  document.querySelectorAll('.edit-only').forEach(el => el.classList.toggle('d-none', !editingMode));
}

async function saveUser() {
  const form = document.getElementById('userForm');
  const name = document.getElementById('uName').value.trim();
  const email = document.getElementById('uEmail').value.trim();
  const currentPassword = document.getElementById('uCurrentPassword').value;
  const newPassword = document.getElementById('uNewPassword').value;
  const confirmPassword = document.getElementById('uPasswordConfirmation').value;

  if (!form.checkValidity()) { form.classList.add('was-validated'); return; }

  const data = { name, email };

  if (editingMode) {
    if (newPassword || confirmPassword) {
      if (!currentPassword) {
        return Swal.fire({ icon:'error', title:'Error', text:'Current password is required to change your password.' });
      }
      if (newPassword.length < 6) {
        return Swal.fire({ icon:'error', title:'Error', text:'New password must be at least 6 characters.' });
      }
      if (newPassword !== confirmPassword) {
        return Swal.fire({ icon:'error', title:'Error', text:'New password and confirmation do not match.' });
      }
      data.current_password = currentPassword;
      data.password = newPassword;
      data.password_confirmation = confirmPassword;
    }
  } else {
    const password = document.getElementById('uPassword').value;
    data.position = document.getElementById('uPosition').value.trim();
    data.department = document.getElementById('uDepartment').value;
    data.gender = document.getElementById('uGender').value;
    data.salary = document.getElementById('uSalary').value ? parseFloat(document.getElementById('uSalary').value) : null;
    data.password = password;
  }

  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const url = editingMode ? `/api/users/${editingUserId}` : '/api/users';
  const method = editingMode ? 'PUT' : 'POST';

  const response = await fetch(url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': token,
      'Accept': 'application/json'
    },
    body: JSON.stringify(data)
  });

  const result = await response.json();
  if (result.success) {
    bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
    ersToast('success', result.message);
    fetchUsers(userSearch.value.trim());
  } else {
    const message = result.errors ? Object.values(result.errors).flat()[0] : 'Unable to save user.';
    Swal.fire({ icon:'error', title:'Error', text: message });
  }
}

async function editUser(id) {
  const response = await fetch(`/api/users/${id}`, {
    headers: {
      'Accept': 'application/json'
    }
  });
  const result = await response.json();
  if (result.success) {
    const { name, email, position, department, gender, salary } = result.user;
    openUserModal(id, name, email, position, department, gender, salary);
    const modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
  } else {
    Swal.fire({ icon:'error', title:'Error', text:'Unable to load user details.' });
  }
}

async function deleteUser(id, btn) {
  ersConfirmDelete('This user will be permanently removed.', async () => {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const response = await fetch(`/api/users/${id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      }
    });
    const result = await response.json();
    if (result.success) {
      btn.closest('tr').remove();
      ersToast('success', result.message);
    }
  });
}

document.getElementById('userSearch').addEventListener('input', e => {
  fetchUsers(e.target.value.trim());
});

fetchUsers();
</script>
@endpush