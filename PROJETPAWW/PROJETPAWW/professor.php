<?php session_start(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Professor Dashboard - Attendance System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    :root{--accent:#2b6cb0;--primary-blue:#1e3a8a;--secondary-teal:#0d9488;--accent-purple:#9333ea;--warm-orange:#f97316;--danger-red:#dc2626;--success-green:#16a34a}
    body{font-family:Inter,system-ui,Arial,Helvetica,sans-serif;background:#001f3f;color:#fff}
    .app{max-width:1400px;margin:18px auto;padding:12px}
    header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;background:linear-gradient(90deg,#003d82 0%,#004a99 100%);padding:20px;border-radius:12px;color:white}
    header h3{margin:0;color:#fff;font-size:24px}
    header .small-muted{color:#b3d9ff}
    .card{border-radius:12px;border-left:4px solid var(--accent-purple);background:#0a2e5c;color:#fff}
    .card h6{color:#4da6ff;font-weight:700}
    .table-responsive{max-height:480px;overflow:auto}
    .present-row{background:#90EE90!important;color:#1a4d1a}
    .present-row td{background:#90EE90!important;color:#1a4d1a}
    .absent-row{background:#FF6B6B!important;color:#8B0000}
    .absent-row td{background:#FF6B6B!important;color:#8B0000}
    .warning-row{background:#FFD700!important;color:#8B6914}
    .warning-row td{background:#FFD700!important;color:#8B6914}
    .table td,.table th{vertical-align:middle!important}
    .card{box-shadow:0 6px 20px rgba(15,23,42,0.1)}
    thead.table-light{background:linear-gradient(90deg,#004a99 0%,#003d82 100%);border-bottom:2px solid #4da6ff}
    thead th{color:#fff;font-weight:700}
    .table-secondary{background:linear-gradient(90deg,#003d82 0%,#002d5c 100%)!important;color:#4da6ff!important}
    .btn-light{background:#0a2e5c;color:#4da6ff;border:1px solid #003d82}
    .btn-light:hover{background:#003d82;color:#fff;border-color:#4da6ff}
    table tbody tr{border-bottom:1px solid #003d82}
    table tbody tr:hover{opacity:0.9!important;box-shadow:inset 0 0 10px rgba(77,166,255,0.2)}
    .search-area{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
    .msg-cell{white-space:normal;color:#4da6ff;font-weight:500}
    footer{color:#7a9cc6;font-weight:500;padding:12px 0;border-top:2px solid #003d82;margin-top:24px}
    .stat-box{background:#0a2e5c;border:1px solid #003d82;padding:15px;border-radius:8px;text-align:center;margin-bottom:12px}
    .stat-box h4{color:#4da6ff;margin:0;font-size:28px}
    .stat-box p{color:#b3d9ff;margin:5px 0 0 0;font-size:12px}
  </style>
</head>
<body>
  <div class="app container">
    <header>
      <div>
        <h3>👨‍🏫 Professor Dashboard</h3>
        <div class="small-muted">Manage attendance, sessions, and student records</div>
      </div>
      <div class="d-flex gap-2">
        <button id="btn-create-session" class="btn btn-outline-success btn-sm">Create Session</button>
        <button id="btn-export-report" class="btn btn-outline-info btn-sm">Export Report</button>
        <button id="btn-logout" class="btn btn-outline-danger btn-sm">Logout</button>
      </div>
    </header>

    <main>
      <!-- Statistics -->
      <div class="row mb-3">
        <div class="col-md-3">
          <div class="stat-box">
            <h4 id="stat-students">0</h4>
            <p>Total Students</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-box">
            <h4 id="stat-sessions">0</h4>
            <p>Sessions Created</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-box">
            <h4 id="stat-avg-attendance">0%</h4>
            <p>Average Attendance</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-box">
            <h4 id="stat-pending-just">0</h4>
            <p>Pending Justifications</p>
          </div>
        </div>
      </div>

      <!-- Active Sessions -->
      <div class="card p-3 mb-3">
        <h6>Active Sessions</h6>
        <div id="sessions-list" class="mb-3"></div>
        <button class="btn btn-primary btn-sm" onclick="openSessionModal()">Start New Session</button>
      </div>

      <!-- Attendance Management -->
      <div class="card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6>Mark Attendance</h6>
          <div class="search-area">
            <input id="search-name" class="form-control form-control-sm" placeholder="Search student..." style="max-width:200px">
            <button id="btn-mark-all" class="btn btn-outline-success btn-sm">Mark All Present</button>
            <button id="btn-clear-all" class="btn btn-outline-secondary btn-sm">Clear All</button>
          </div>
        </div>

        <div class="table-responsive">
          <table id="attendance-main-table" class="table table-bordered table-sm align-middle">
            <thead class="table-light">
              <tr>
                <th>Student Name</th>
                <th colspan="6" class="text-center">Sessions</th>
                <th class="text-center">Absences</th>
                <th class="text-center">Participation</th>
                <th class="text-center">Status</th>
              </tr>
              <tr class="table-secondary text-center">
                <th></th>
                <th>S1</th><th>S2</th><th>S3</th><th>S4</th><th>S5</th><th>S6</th>
                <th></th><th></th><th></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- Pending Justifications -->
      <div class="card p-3 mb-3">
        <h6>Pending Justifications</h6>
        <div id="justifications-list" class="mb-3"></div>
      </div>

      <!-- Class Statistics -->
      <div class="row">
        <div class="col-md-6">
          <div class="card p-3 mb-3">
            <h6>Attendance Distribution</h6>
            <canvas id="attendance-chart" height="140"></canvas>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-3 mb-3">
            <h6>Student Performance</h6>
            <canvas id="performance-chart" height="140"></canvas>
          </div>
        </div>
      </div>
    </main>

    <footer class="text-center small-muted mt-2">Professor Attendance Management System</footer>
  </div>

  <!-- Session Modal -->
  <div class="modal fade" id="sessionModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content" style="background:#0a2e5c;color:white;border:1px solid #003d82">
        <div class="modal-header" style="border-bottom:1px solid #003d82">
          <h5 class="modal-title">Create New Session</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Session Code (Auto-generated)</label>
            <input type="text" class="form-control" id="session-code" style="background:#003d82;color:white;border:1px solid #4da6ff" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" id="session-date" style="background:#003d82;color:white;border:1px solid #4da6ff" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Course</label>
            <select class="form-control" id="session-course" style="background:#003d82;color:white;border:1px solid #4da6ff" required>
              <option>-- Select Course --</option>
              <option>Data Structures</option>
              <option>Web Development</option>
              <option>Database Design</option>
            </select>
          </div>
          <button type="button" class="btn btn-success btn-sm" onclick="createSession()">Create Session</button>
        </div>
      </div>
    </div>
  </div>

<script>
const sessionCount = 6;

function initPage() { loadStudentData(); updateStatistics(); renderCharts(); loadJustifications(); loadSessions(); }

function loadStudentData() {
  const tbody = $('#attendance-main-table tbody').empty();
  
  const students = [ {id: 's1', name: 'Ahmed Sara'}, {id: 's2', name: 'Yacine Amira'}, {id: 's3', name: 'Anes Lyna'}, {id: 's4', name: 'Karim Fatima'}, {id: 's5', name: 'Nour Zahra'} ];

  students.forEach(student => {
    const tr = $('<tr>').attr('data-id', student.id);
    tr.append($('<td>').text(student.name));
    
    let absences = 0;
    for(let i = 0; i < 6; i++) { const present = Math.random() > 0.3; if(!present) absences++; const checkbox = $('<input type="checkbox">').prop('checked', present); tr.append($('<td class="text-center">').append(checkbox)); }
    
    const participations = Math.floor(Math.random() * 6);
    const status = absences <= 1 ? 'Good' : absences <= 3 ? 'Fair' : 'Poor';
    
    tr.append($('<td class="text-center">').text(absences));
    tr.append($('<td class="text-center">').text(participations));
    tr.append($('<td class="text-center">').text(status));
    
    if(absences <= 1) tr.addClass('present-row'); else if(absences <= 3) tr.addClass('warning-row'); else tr.addClass('absent-row');
    
    tbody.append(tr);
  });
}

function updateStatistics() { $('#stat-students').text(5); $('#stat-sessions').text(6); $('#stat-avg-attendance').text('85%'); $('#stat-pending-just').text(2); }

function renderCharts() {
  const ctx1 = document.getElementById('attendance-chart').getContext('2d'); if(window.attendanceChart) window.attendanceChart.destroy();
  window.attendanceChart = new Chart(ctx1, { type: 'doughnut', data: { labels: ['Present', 'Absent', 'Excused'], datasets: [{ data: [18, 3, 2], backgroundColor: ['#90EE90', '#FF6B6B', '#FFD700'], borderColor: ['#1a4d1a', '#8B0000', '#8B6914'], borderWidth: 2 }] }, options: {responsive: true} });

  const ctx2 = document.getElementById('performance-chart').getContext('2d'); if(window.performanceChart) window.performanceChart.destroy();
  window.performanceChart = new Chart(ctx2, { type: 'bar', data: { labels: ['Ahmed', 'Yacine', 'Anes', 'Karim', 'Nour'], datasets: [{ label: 'Attendance %', data: [100, 83, 67, 83, 100], backgroundColor: '#4da6ff', borderColor: '#002d5c', borderWidth: 1 }] }, options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } } });
}

function loadJustifications() { const list = $('#justifications-list').empty(); const justifications = [ {student: 'Ahmed Sara', date: '2025-01-15', reason: 'Medical appointment', status: 'Pending'}, {student: 'Anes Lyna', date: '2025-02-05', reason: 'Family emergency', status: 'Pending'} ]; if(justifications.length === 0) { list.html('<p class="text-muted">No pending justifications.</p>'); return; } justifications.forEach(j => { const card = $(`<div class="card p-2 mb-2" style="border-left:3px solid #FFD700"><div class="d-flex justify-content-between"><div><strong>${j.student}</strong> - ${j.date}<br><small>${j.reason}</small></div><div class="d-flex gap-1"><button class="btn btn-sm btn-success" onclick="approveJustification('${j.student}')">Approve</button><button class="btn btn-sm btn-danger" onclick="rejectJustification('${j.student}')">Reject</button></div></div></div>`); list.append(card); }); }

function loadSessions() { const list = $('#sessions-list').empty(); const sessions = [ {code: 'SES001', date: '2025-01-15', status: 'Active'}, {code: 'SES002', date: '2025-01-22', status: 'Closed'} ]; sessions.forEach(s => { const card = $(`<div class="card p-2 mb-2" style="border:1px solid #4da6ff"><div class="d-flex justify-content-between align-items-center"><div><strong>${s.code}</strong> | ${s.date}</div><span class="badge" style="background:${s.status === 'Active' ? '#10b981' : '#6b7280'}">${s.status}</span></div></div>`); list.append(card); }); }

function openSessionModal() { const code = 'SES' + Math.random().toString(36).substr(2, 9).toUpperCase(); $('#session-code').val(code); $('#sessionModal').modal('show'); }
function createSession() { alert('Session created successfully!'); $('#sessionModal').modal('hide'); loadSessions(); }
function approveJustification(student) { alert('Justification from ' + student + ' approved!'); loadJustifications(); }
function rejectJustification(student) { alert('Justification from ' + student + ' rejected!'); loadJustifications(); }
// Update row state (absences, status, classes) based on checkboxes
function updateRowState($tr) {
  const $row = $($tr);
  const $checkboxes = $row.find('input[type="checkbox"]');
  const checkedCount = $checkboxes.filter(':checked').length;
  const absences = $checkboxes.length - checkedCount;

  // column indexes: name=0, sessions 1..sessionCount, absences = 1+sessionCount, participation = 2+sessionCount, status = 3+sessionCount
  const absIndex = 1 + sessionCount;
  const partIndex = absIndex + 1;
  const statusIndex = absIndex + 2;

  $row.find('td').eq(absIndex).text(absences);

  let status = 'Good';
  if (absences <= 1) status = 'Good';
  else if (absences <= 3) status = 'Fair';
  else status = 'Poor';

  $row.find('td').eq(statusIndex).text(status);

  $row.removeClass('present-row warning-row absent-row');
  if (absences <= 1) $row.addClass('present-row');
  else if (absences <= 3) $row.addClass('warning-row');
  else $row.addClass('absent-row');
}

function updateAllRows() {
  $('#attendance-main-table tbody tr').each(function(){ updateRowState($(this)); });
}

$(function(){
  initPage();

  // Delegate checkbox change so dynamic rows are covered
  $('#attendance-main-table').on('change', 'input[type="checkbox"]', function(){
    const $tr = $(this).closest('tr');
    updateRowState($tr);
  });

  $('#btn-create-session').on('click', openSessionModal);

  $('#btn-mark-all').on('click', function(){
    $('#attendance-main-table tbody tr').each(function(){
      $(this).find('input[type="checkbox"]').prop('checked', true);
    });
    updateAllRows();
  });

  $('#btn-clear-all').on('click', function(){
    $('#attendance-main-table tbody tr').each(function(){
      $(this).find('input[type="checkbox"]').prop('checked', false);
    });
    updateAllRows();
  });

  $('#search-name').on('input', function(){ const q = $(this).val().toLowerCase(); $('#attendance-main-table tbody tr').each(function(){ const name = $(this).find('td:first').text().toLowerCase(); $(this).toggle(name.includes(q)); }); });

  $('#btn-logout').on('click', function(){ window.location.href = 'login.php'; });

  // Ensure initial row classes match checkbox state
  updateAllRows();
});
</script>

<!-- Bootstrap JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
