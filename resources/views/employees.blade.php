@extends('layouts.main')

@section('content')

<div id="ers-layout-root" data-title="Employee Records" data-active="employees">

  <div class="ers-card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
      <div class="d-flex gap-2 flex-wrap">
        <div class="input-group" style="max-width:260px">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input id="empSearch" class="form-control" placeholder="Search employees...">
        </div>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#empModal" onclick="openEmpModal()">
        <i class="bi bi-plus-lg"></i> Add Employee
      </button>
    </div>
    <div class="table-responsive">
      <table class="table mb-0" id="empTable">
        <thead><tr><th>Employee ID</th><th>Position</th><th>Department</th><th>Gender</th><th>Salary</th><th>Hire Date</th><th class="text-end">Actions</th></tr></thead>
        <tbody></tbody>
      </table>
      <div class="card-body d-flex justify-content-between align-items-center"></div>
    </div>
  </div>

</div>

<!-- Employee Modal -->
<div class="modal fade" id="empModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="empModalTitle">Add Employee</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="empForm" novalidate>
          @csrf
          <input type="hidden" id="eId" name="id">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Employee ID</label><input id="eEmployeeId" name="employee_id" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Position</label><input id="ePosition" name="position" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Department</label><input id="eDepartment" name="department" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Gender</label>
              <select id="eGender" name="gender" class="form-select" required>
                <option value="">Choose...</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Salary</label><input id="eSalary" name="salary" type="number" step="0.01" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Hire Date</label><input id="eHireDate" name="hire_date" type="date" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input id="ePhone" name="phone" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Employment Status</label>
              <select id="eEmploymentStatus" name="employment_status" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                <option value="On Leave">On Leave</option>
                <option value="Terminated">Terminated</option>
              </select>
            </div>
            <div class="col-12"><label class="form-label">Address</label><textarea id="eAddress" name="address" class="form-control" rows="2"></textarea></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea id="eNotes" name="notes" class="form-control" rows="2"></textarea></div>
          </div>
        </form>
      </div>
      <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" onclick="saveEmp()">Save</button></div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
let empEditing = false;
let editingEmpId = null;
const empTableBody = document.querySelector('#empTable tbody');
const empSearch = document.getElementById('empSearch');

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

function renderEmployees(employees) {
  empTableBody.innerHTML = employees.map(emp => `
    <tr data-emp-id="${emp.id}">
      <td>${escapeHtml(emp.employee_id)}</td>
      <td>${escapeHtml(emp.position)}</td>
      <td>${escapeHtml(emp.department)}</td>
      <td>${escapeHtml(emp.gender)}</td>
      <td>$${parseFloat(emp.salary).toFixed(2)}</td>
      <td>${new Date(emp.hire_date).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' })}</td>
      <td class="text-end">
        <button class="btn btn-soft btn-icon me-1" onclick="editEmp(${emp.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-icon" style="background:#fee2e2;color:#dc2626" onclick="deleteEmp(${emp.id}, this)"><i class="bi bi-trash"></i></button>
      </td>
    </tr>
  `).join('');
}

async function fetchEmployees(query = '') {
  const url = query ? `/api/employees?q=${encodeURIComponent(query)}` : '/api/employees';
  const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
  const result = await response.json();
  if (result.success) {
    renderEmployees(result.employees);
  }
}

function openEmpModal(id = null, employeeId = '', position = '', department = '', gender = '', salary = '', hireDate = '', phone = '', employmentStatus = 'Active', address = '', notes = '') {
  empEditing = !!id;
  editingEmpId = id;
  document.getElementById('empModalTitle').textContent = empEditing ? 'Edit Employee' : 'Add Employee';
  document.getElementById('eId').value = id || '';
  document.getElementById('eEmployeeId').value = employeeId;
  document.getElementById('ePosition').value = position;
  document.getElementById('eDepartment').value = department;
  document.getElementById('eGender').value = gender;
  document.getElementById('eSalary').value = salary;
  document.getElementById('eHireDate').value = hireDate;
  document.getElementById('ePhone').value = phone;
  document.getElementById('eEmploymentStatus').value = employmentStatus;
  document.getElementById('eAddress').value = address;
  document.getElementById('eNotes').value = notes;
  document.getElementById('empForm').classList.remove('was-validated');
}

async function saveEmp() {
  const form = document.getElementById('empForm');
  if (!form.checkValidity()) { form.classList.add('was-validated'); return; }

  const data = {
    employee_id: document.getElementById('eEmployeeId').value.trim(),
    position: document.getElementById('ePosition').value.trim(),
    department: document.getElementById('eDepartment').value.trim(),
    gender: document.getElementById('eGender').value,
    salary: parseFloat(document.getElementById('eSalary').value),
    hire_date: document.getElementById('eHireDate').value,
    phone: document.getElementById('ePhone').value.trim(),
    employment_status: document.getElementById('eEmploymentStatus').value,
    address: document.getElementById('eAddress').value.trim(),
    notes: document.getElementById('eNotes').value.trim(),
  };

  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const url = empEditing ? `/api/employees/${editingEmpId}` : '/api/employees';
  const method = empEditing ? 'PUT' : 'POST';

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
    bootstrap.Modal.getInstance(document.getElementById('empModal')).hide();
    ersToast('success', result.message);
    fetchEmployees(empSearch.value.trim());
  } else {
    const message = result.errors ? Object.values(result.errors).flat()[0] : 'Unable to save employee.';
    Swal.fire({ icon:'error', title:'Error', text: message });
  }
}

async function editEmp(id) {
  const response = await fetch(`/api/employees/${id}`, {
    headers: {
      'Accept': 'application/json'
    }
  });
  const result = await response.json();
  if (result.success) {
    const emp = result.employee;
    openEmpModal(emp.id, emp.employee_id, emp.position, emp.department, emp.gender, emp.salary, emp.hire_date, emp.phone, emp.employment_status, emp.address, emp.notes);
    const modal = new bootstrap.Modal(document.getElementById('empModal'));
    modal.show();
  } else {
    Swal.fire({ icon:'error', title:'Error', text:'Unable to load employee details.' });
  }
}

async function deleteEmp(id, btn) {
  ersConfirmDelete('This employee record will be permanently removed.', async () => {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const response = await fetch(`/api/employees/${id}`, {
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

document.getElementById('empSearch').addEventListener('input', e => {
  fetchEmployees(e.target.value.trim());
});

fetchEmployees();
</script>
@endpush