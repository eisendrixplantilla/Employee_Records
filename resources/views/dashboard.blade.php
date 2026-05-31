@extends('layouts.main')

@section('content')

<div id="ers-layout-root" data-title="Dashboard" data-active="dashboard">

  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="summary-card">
        <div class="icon bg-blue"><i class="bi bi-people-fill"></i></div>
        <div><p class="label">Total Users</p><p class="value" id="totalUsersValue">0</p></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="summary-card">
        <div class="icon bg-green"><i class="bi bi-person-badge-fill"></i></div>
        <div><p class="label">Total Employees</p><p class="value" id="totalEmployeesValue">0</p></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="summary-card">
        <div class="icon bg-purple"><i class="bi bi-building"></i></div>
        <div><p class="label">Total Departments</p><p class="value" id="totalDepartmentsValue">0</p></div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="summary-card">
        <div class="icon bg-orange"><i class="bi bi-graph-up-arrow"></i></div>
        <div><p class="label">New This Month</p><p class="value" id="newThisMonthValue">0</p></div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-lg-7">
      <div class="ers-card h-100">
        <div class="card-header">Employee Count by Department</div>
        <div class="card-body"><canvas id="barChart" height="120"></canvas></div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="ers-card h-100">
        <div class="card-header">Department Distribution</div>
        <div class="card-body"><canvas id="pieChart" height="120"></canvas></div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="ers-card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Recent Employees</span>
          <a href="employees" class="small text-decoration-none">View all</a>
        </div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead><tr><th>Employee ID</th><th>Position</th><th>Department</th><th>Date Hired</th></tr></thead>
            <tbody id="recentEmployeesBody">
              <tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="ers-card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Latest Registered Users</span>
          <a href="users" class="small text-decoration-none">View all</a>
        </div>
        <ul class="list-group list-group-flush" id="recentUsersList">
          <li class="list-group-item text-center text-muted">Loading...</li>
        </ul>
      </div>
    </div>
  </div>

</div>

@endsection

@push('scripts')
<script>
let barChartInstance = null;
let pieChartInstance = null;

function getInitials(name) {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
}

function getRandomColor() {
  const colors = ['#2563eb', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#64748b', '#ec4899', '#f97316', '#14b8a6'];
  return colors[Math.floor(Math.random() * colors.length)];
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function getTimeAgo(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);
  
  if (seconds < 60) return 'just now';
  if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
  if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
  return Math.floor(seconds / 86400) + 'd ago';
}

async function loadDashboardStats() {
  try {
    const response = await fetch('/api/dashboard/stats', {
      headers: { 'Accept': 'application/json' }
    });
    const result = await response.json();
    
    if (result.success) {
      document.getElementById('totalUsersValue').textContent = result.stats.total_users;
      document.getElementById('totalEmployeesValue').textContent = result.stats.total_employees;
      document.getElementById('totalDepartmentsValue').textContent = result.stats.total_departments;
      document.getElementById('newThisMonthValue').textContent = result.stats.new_this_month;
    }
  } catch (error) {
    console.error('Error loading stats:', error);
  }
}

async function loadRecentEmployees() {
  try {
    const response = await fetch('/api/dashboard/recent-employees', {
      headers: { 'Accept': 'application/json' }
    });
    const result = await response.json();
    
    if (result.success) {
      const tbody = document.getElementById('recentEmployeesBody');
      if (result.employees.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No employees found</td></tr>';
      } else {
        tbody.innerHTML = result.employees.map(emp => `
          <tr>
            <td>${emp.employee_id}</td>
            <td>${emp.position}</td>
            <td>${emp.department}</td>
            <td>${formatDate(emp.hire_date)}</td>
          </tr>
        `).join('');
      }
    }
  } catch (error) {
    console.error('Error loading recent employees:', error);
  }
}

async function loadRecentUsers() {
  try {
    const response = await fetch('/api/dashboard/recent-users', {
      headers: { 'Accept': 'application/json' }
    });
    const result = await response.json();
    
    if (result.success) {
      const list = document.getElementById('recentUsersList');
      if (result.users.length === 0) {
        list.innerHTML = '<li class="list-group-item text-center text-muted">No users found</li>';
      } else {
        const colors = ['#2563eb', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444'];
        list.innerHTML = result.users.map((user, idx) => `
          <li class="list-group-item d-flex align-items-center gap-3">
            <span class="avatar" style="width:38px;height:38px;border-radius:50%;background:${colors[idx % colors.length]};color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:600;font-size:12px">${getInitials(user.name)}</span>
            <div class="flex-fill"><div class="fw-semibold">${user.name}</div><div class="small text-muted">${user.email}</div></div>
            <span class="small text-muted">${getTimeAgo(user.created_at)}</span>
          </li>
        `).join('');
      }
    }
  } catch (error) {
    console.error('Error loading recent users:', error);
  }
}

async function loadEmployeesByDepartment() {
  try {
    const response = await fetch('/api/dashboard/employees-by-department', {
      headers: { 'Accept': 'application/json' }
    });
    const result = await response.json();
    
    if (result.success) {
      if (result.labels.length === 0) {
        document.getElementById('barChart').parentElement.innerHTML = '<p class="text-center text-muted py-4">No department data yet</p>';
        document.getElementById('pieChart').parentElement.innerHTML = '<p class="text-center text-muted py-4">No department data yet</p>';
        return;
      }

      const colors = ['#2563eb', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#64748b', '#ec4899'];

      if (barChartInstance) barChartInstance.destroy();
      if (pieChartInstance) pieChartInstance.destroy();
      
      barChartInstance = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
          labels: result.labels,
          datasets: [{
            label: 'Employees',
            data: result.counts,
            backgroundColor: '#2563eb',
            borderRadius: 6
          }]
        },
        options: {
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
      });
      
      pieChartInstance = new Chart(document.getElementById('pieChart'), {
        type: 'doughnut',
        data: {
          labels: result.labels,
          datasets: [{
            data: result.counts,
            backgroundColor: colors.slice(0, result.labels.length)
          }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
      });
    }
  } catch (error) {
    console.error('Error loading employees by department:', error);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  loadDashboardStats();
  loadRecentEmployees();
  loadRecentUsers();
  loadEmployeesByDepartment();
});
</script>
@endpush