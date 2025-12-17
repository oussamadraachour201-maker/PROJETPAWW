<?php session_start(); ?>
<?php
// Load students for Admin students management tab
require_once __DIR__ . '/db_connect.php';
$studentsList = [];
try {
  $db = get_db_connection();
  $stmt = $db->query('SELECT id, fullname, matricule, group_id, created_at FROM students ORDER BY fullname');
  $studentsList = $stmt->fetchAll();
} catch (Exception $e) {
  // ignore - we'll show empty list
  $studentsList = [];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard - Attendance System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    :root{--accent:#2b6cb0;--primary-blue:#1e3a8a;--secondary-teal:#0d9488;--accent-purple:#9333ea;--warm-orange:#f97316;--danger-red:#dc2626;--success-green:#16a34a}
    body{font-family:Inter,system-ui,Arial,Helvetica,sans-serif;background:#001f3f;color:#fff}
    .app{max-width:1400px;margin:18px auto;padding:12px}
    header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;background:linear-gradient(90deg,#7f1d1d 0%,#991b1b 100%);padding:20px;border-radius:12px;color:white}
    header h3{margin:0;color:#fff;font-size:24px}
    header .small-muted{color:#fca5a5}
    .card{border-radius:12px;border-left:4px solid #dc2626;background:#0a2e5c;color:#fff}
    .card h6{color:#4da6ff;font-weight:700}
    .table-responsive{max-height:480px;overflow:auto}
    .card{box-shadow:0 6px 20px rgba(15,23,42,0.1)}
    thead.table-light{background:linear-gradient(90deg,#004a99 0%,#003d82 100%);border-bottom:2px solid #4da6ff}
    thead th{color:#fff;font-weight:700}
    .table-secondary{background:linear-gradient(90deg,#003d82 0%,#002d5c 100%)!important;color:#4da6ff!important}
    .btn-light{background:#0a2e5c;color:#4da6ff;border:1px solid #003d82}
    .btn-light:hover{background:#003d82;color:#fff;border-color:#4da6ff}
    table tbody tr{border-bottom:1px solid #003d82}
    table tbody tr:hover{opacity:0.9!important;box-shadow:inset 0 0 10px rgba(77,166,255,0.2)}
    .msg-cell{white-space:normal;color:#4da6ff;font-weight:500}
    footer{color:#7a9cc6;font-weight:500;padding:12px 0;border-top:2px solid #003d82;margin-top:24px}
    .stat-card{padding:20px;border-radius:8px;text-align:center;margin-bottom:12px;border:1px solid #dc2626}
    .stat-card h4{color:#fca5a5;font-size:28px;margin:0}
    .stat-card p{color:#fed7d7;margin:5px 0 0 0;font-size:12px}
    .stat-card.blue{border:1px solid #4da6ff}
    .stat-card.blue h4{color:#4da6ff}
    .stat-card.blue p{color:#b3d9ff}
    .nav-tabs{border-bottom:2px solid #003d82}
    .nav-tabs .nav-link{color:#7a9cc6;border:1px solid transparent;border-bottom:2px solid transparent}
    .nav-tabs .nav-link.active{color:#4da6ff;border:1px solid #003d82;border-bottom:2px solid #4da6ff;background:transparent}
    .tab-content{background:#0a2e5c;padding:20px;border-radius:8px}
  </style>
</head>
<body>
  <div class="app container">
    <header>
      <div>
        <h3>⚙️ Administration Dashboard</h3>
        <div class="small-muted">Full system management and oversight</div>
      </div>
      <div class="d-flex gap-2">
        <button id="btn-system-settings" class="btn btn-outline-light btn-sm">System Settings</button>
        <button id="btn-backup" class="btn btn-outline-light btn-sm">Backup Database</button>
        <button id="btn-logout" class="btn btn-outline-danger btn-sm">Logout</button>
      </div>
    </header>

    <main>
      <!-- Statistics -->
      <div class="row mb-3">
        <div class="col-md-3">
          <div class="stat-card">
            <h4 id="stat-users">0</h4>
            <p>Total Users</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card blue">
            <h4 id="stat-courses">0</h4>
            <p>Active Courses</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card blue">
            <h4 id="stat-classes">0</h4>
            <p>Classes</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card">
            <h4 id="stat-reports">0</h4>
            <p>Generated Reports</p>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <ul class="nav nav-tabs mb-3" id="adminTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="users-tab" role="tab" aria-selected="true" aria-controls="users-panel" data-bs-toggle="tab" data-bs-target="#users-panel">Users</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="students-tab" role="tab" aria-selected="false" aria-controls="students-panel" data-bs-toggle="tab" data-bs-target="#students-panel">Students</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="courses-tab" role="tab" aria-selected="false" aria-controls="courses-panel" data-bs-toggle="tab" data-bs-target="#courses-panel">Courses</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="groups-tab" role="tab" aria-selected="false" aria-controls="groups-panel" data-bs-toggle="tab" data-bs-target="#groups-panel">Groups</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="reports-tab" role="tab" aria-selected="false" aria-controls="reports-panel" data-bs-toggle="tab" data-bs-target="#reports-panel">Reports</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="logs-tab" role="tab" aria-selected="false" aria-controls="logs-panel" data-bs-toggle="tab" data-bs-target="#logs-panel">System Logs</button>
        </li>
      </ul>

      <div class="tab-content" id="adminTabContent">
        <!-- Users Management -->
        <div class="tab-pane fade show active" id="users-panel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6>User Management</h6>
            <button class="btn btn-primary btn-sm" onclick="openUserModal()">Add User</button>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>User ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="users-list"></tbody>
            </table>
          </div>
        </div>

        <!-- Students Management -->
        <div class="tab-pane fade" id="students-panel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6>Students</h6>
            <div>
              <a href="add_student.php" class="btn btn-primary btn-sm">Add Student</a>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>ID</th>
                  <th>Matricule</th>
                  <th>Full Name</th>
                  <th>Group</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
              <?php if (empty($studentsList)): ?>
                <tr><td colspan="6" class="text-center">No students found.</td></tr>
              <?php else: ?>
                <?php foreach ($studentsList as $s): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($s['id']); ?></td>
                    <td><?php echo htmlspecialchars($s['matricule']); ?></td>
                    <td><?php echo htmlspecialchars($s['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($s['group_id']); ?></td>
                    <td><?php echo htmlspecialchars($s['created_at'] ?? ''); ?></td>
                    <td>
                      <a href="update_student.php?id=<?php echo urlencode($s['id']); ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                      <a href="delete_student.php?id=<?php echo urlencode($s['id']); ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Courses Management -->
        <div class="tab-pane fade" id="courses-panel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6>Course Management</h6>
            <button class="btn btn-primary btn-sm" onclick="openCourseModal()">Add Course</button>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>Course ID</th>
                  <th>Course Name</th>
                  <th>Professor</th>
                  <th>Credits</th>
                  <th>Semester</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="courses-list"></tbody>
            </table>
          </div>
        </div>

        <!-- Groups Management -->
        <div class="tab-pane fade" id="groups-panel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6>Group Management</h6>
            <button class="btn btn-primary btn-sm" onclick="openGroupModal()">Create Group</button>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>Group ID</th>
                  <th>Course</th>
                  <th>Professor</th>
                  <th>Students</th>
                  <th>Capacity</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="groups-list"></tbody>
            </table>
          </div>
        </div>

        <!-- Reports -->
        <div class="tab-pane fade" id="reports-panel">
          <h6 class="mb-3">System Reports</h6>
          <div class="row">
            <div class="col-md-6">
              <div class="card p-3 mb-3">
                <h6>Attendance Overview</h6>
                <canvas id="report-chart-1" height="120"></canvas>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card p-3 mb-3">
                <h6>User Distribution</h6>
                <canvas id="report-chart-2" height="120"></canvas>
              </div>
            </div>
          </div>
          <div class="card p-3 mb-3">
            <h6>System Statistics</h6>
            <div id="system-stats"></div>
          </div>
        </div>

        <!-- System Logs -->
        <div class="tab-pane fade" id="logs-panel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6>System Logs</h6>
            <button class="btn btn-outline-secondary btn-sm" onclick="clearLogs()">Clear Logs</button>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>Timestamp</th>
                  <th>User</th>
                  <th>Action</th>
                  <th>Details</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="logs-list"></tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <footer class="text-center small-muted mt-2">Administration Panel | Attendance Management System</footer>
  </div>

  <!-- User Modal -->
  <div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content" style="background:#0a2e5c;color:white;border:1px solid #003d82">
        <div class="modal-header" style="border-bottom:1px solid #003d82">
          <h5 class="modal-title">Add New User</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" id="user-email" style="background:#003d82;color:white;border:1px solid #4da6ff" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" id="user-name" style="background:#003d82;color:white;border:1px solid #4da6ff" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select class="form-control" id="user-role" style="background:#003d82;color:white;border:1px solid #4da6ff" required>
                <option value="">-- Select Role --</option>
              <option value="student">Student</option>
              <option value="professor">Professor</option>
              <option value="admin">Administrator</option>
            </select>
          </div>
          <button type="button" class="btn btn-success btn-sm" onclick="addUser()">Create User</button>
        </div>
      </div>
    </div>
  </div>

<script>
// System settings and backup handlers
function openSystemSettings() {
  // Load existing settings via AJAX
  fetch('save_settings.php?action=get')
    .then(r => r.json())
    .then(data => {
      if (data && data.settings) {
        $('#setting-site-title').val(data.settings.site_title || 'Attendance System');
        $('#setting-admin-email').val(data.settings.admin_email || '');
      }
      const modalEl = document.getElementById('systemSettingsModal');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }).catch(err => {
      alert('Failed to load settings');
    });
}

function saveSettings() {
  const payload = {
    site_title: $('#setting-site-title').val(),
    admin_email: $('#setting-admin-email').val()
  };
  fetch('save_settings.php', {method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)})
    .then(r => r.json())
    .then(res => {
      if (res && res.success) {
        alert('Settings saved');
        const modalEl = document.getElementById('systemSettingsModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
      } else {
        alert('Failed to save settings');
      }
    }).catch(err => { alert('Save failed'); });
}

function doBackup() {
  // Trigger download of backup SQL
  window.location.href = 'backup_db.php';
}

function initPage() { updateStatistics(); loadUsers(); loadCourses(); loadGroups(); loadLogs(); renderReports(); }
function updateStatistics() { $('#stat-users').text(15); $('#stat-courses').text(6); $('#stat-classes').text(8); $('#stat-reports').text(24); }
function loadUsers() { const list = $('#users-list').empty(); const users = [ {id: 'U001', name: 'Ahmed Karim', email: 'ahmed@univ.dz', role: 'Professor', status: 'Active', created: '2025-01-01'}, {id: 'U002', name: 'Fatima Noor', email: 'fatima@univ.dz', role: 'Student', status: 'Active', created: '2025-01-05'}, {id: 'U003', name: 'Admin User', email: 'admin@univ.dz', role: 'Administrator', status: 'Active', created: '2024-12-01'} ]; users.forEach(u => { const row = $(`<tr><td>${u.id}</td><td>${u.name}</td><td>${u.email}</td><td><span class="badge bg-primary">${u.role}</span></td><td><span class="badge bg-success">${u.status}</span></td><td>${u.created}</td><td><button class="btn btn-sm btn-outline-warning" onclick="editUser('${u.id}')">Edit</button> <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${u.id}')">Delete</button></td></tr>`); list.append(row); }); }
function loadCourses() { const list = $('#courses-list').empty(); const courses = [ {id: 'C001', name: 'Data Structures', prof: 'Ahmed Karim', credits: 3, semester: 'Spring 2025'}, {id: 'C002', name: 'Web Development', prof: 'Amira Yacine', credits: 3, semester: 'Spring 2025'}, {id: 'C003', name: 'Database Design', prof: 'Nabil Samir', credits: 4, semester: 'Spring 2025'} ]; courses.forEach(c => { const row = $(`<tr><td>${c.id}</td><td>${c.name}</td><td>${c.prof}</td><td>${c.credits}</td><td>${c.semester}</td><td><button class="btn btn-sm btn-outline-warning" onclick="editCourse('${c.id}')">Edit</button> <button class="btn btn-sm btn-outline-danger" onclick="deleteCourse('${c.id}')">Delete</button></td></tr>`); list.append(row); }); }
function loadGroups() { const list = $('#groups-list').empty(); const groups = [ {id: 'G001', course: 'Data Structures', prof: 'Ahmed Karim', students: 28, capacity: 30}, {id: 'G002', course: 'Web Development', prof: 'Amira Yacine', students: 25, capacity: 30}, {id: 'G003', course: 'Database Design', prof: 'Nabil Samir', students: 32, capacity: 35} ]; groups.forEach(g => { const row = $(`<tr><td>${g.id}</td><td>${g.course}</td><td>${g.prof}</td><td>${g.students}</td><td>${g.capacity}</td><td><button class="btn btn-sm btn-outline-info" onclick="viewGroup('${g.id}')">View</button></td></tr>`); list.append(row); }); }
function loadLogs() { const list = $('#logs-list').empty(); const logs = [ {time: '2025-02-27 14:32', user: 'Ahmed Karim', action: 'Create Session', details: 'SES001', status: '✓'}, {time: '2025-02-27 14:15', user: 'Fatima Noor', action: 'Submit Justification', details: 'JUS042', status: '✓'}, {time: '2025-02-27 13:45', user: 'Admin User', action: 'Add User', details: 'U002', status: '✓'} ]; logs.forEach(log => { const row = $(`<tr><td>${log.time}</td><td>${log.user}</td><td>${log.action}</td><td>${log.details}</td><td><span class="badge bg-success">${log.status}</span></td></tr>`); list.append(row); }); }
function renderReports() { const ctx1 = document.getElementById('report-chart-1').getContext('2d'); new Chart(ctx1, { type: 'pie', data: { labels: ['Present', 'Absent', 'Excused'], datasets: [{ data: [85, 10, 5], backgroundColor: ['#90EE90', '#FF6B6B', '#FFD700'] }] }, options: {responsive: true} }); const ctx2 = document.getElementById('report-chart-2').getContext('2d'); new Chart(ctx2, { type: 'bar', data: { labels: ['Students', 'Professors', 'Admins'], datasets: [{ label: 'Count', data: [420, 45, 5], backgroundColor: '#4da6ff', borderColor: '#002d5c', borderWidth: 1 }] }, options: {responsive: true, scales: {y: {beginAtZero: true}}} }); const stats = `<div class="row"><div class="col-md-4"><div class="p-3" style="background:#003d82;border-radius:8px"><strong>Total Sessions:</strong> 48</div></div><div class="col-md-4"><div class="p-3" style="background:#003d82;border-radius:8px"><strong>Avg Attendance:</strong> 85%</div></div><div class="col-md-4"><div class="p-3" style="background:#003d82;border-radius:8px"><strong>Pending Tasks:</strong> 7</div></div></div>`; $('#system-stats').html(stats); }
function openUserModal() { const modalEl = document.getElementById('userModal'); const modal = bootstrap.Modal.getOrCreateInstance(modalEl); modal.show(); }
function addUser() { const email = $('#user-email').val(); const name = $('#user-name').val(); const role = $('#user-role').val(); if(!email || !name || !role) { alert('Please fill all fields'); return; } alert('User created successfully!'); const modalEl = document.getElementById('userModal'); const modal = bootstrap.Modal.getOrCreateInstance(modalEl); modal.hide(); loadUsers(); }
function editUser(id) { alert('Edit user ' + id); }
function deleteUser(id) { if(confirm('Delete this user?')) { alert('User deleted'); loadUsers(); } }
function openCourseModal() { alert('Open course creation modal'); }
function editCourse(id) { alert('Edit course ' + id); }
function deleteCourse(id) { if(confirm('Delete this course?')) { alert('Course deleted'); loadCourses(); } }
function openGroupModal() { alert('Open group creation modal'); }
function viewGroup(id) { alert('View group ' + id); }
function clearLogs() { if(confirm('Clear all system logs?')) { alert('Logs cleared'); loadLogs(); } }

$(function(){
  initPage();
  $('#btn-logout').on('click', function(){ window.location.href = 'logout.php'; });
  $('#btn-system-settings').on('click', openSystemSettings);
  $('#btn-backup').on('click', function(){ if (confirm('Create database backup now?')) doBackup(); });
});
</script>

<!-- System Settings Modal -->
<div class="modal fade" id="systemSettingsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="background:#0a2e5c;color:white;border:1px solid #003d82">
      <div class="modal-header" style="border-bottom:1px solid #003d82">
        <h5 class="modal-title">System Settings</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Site Title</label>
          <input id="setting-site-title" class="form-control" style="background:#003d82;color:white;border:1px solid #4da6ff">
        </div>
        <div class="mb-3">
          <label class="form-label">Administrator Email</label>
          <input id="setting-admin-email" class="form-control" style="background:#003d82;color:white;border:1px solid #4da6ff">
        </div>
        <div class="d-flex justify-content-end">
          <button class="btn btn-secondary btn-sm me-2" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-success btn-sm" onclick="saveSettings()">Save Settings</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
