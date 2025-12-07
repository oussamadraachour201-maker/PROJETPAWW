<?php session_start(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Student Dashboard - Attendance System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    :root{--accent:#2b6cb0;--primary-blue:#1e3a8a;--secondary-teal:#0d9488;--accent-purple:#9333ea;--warm-orange:#f97316;--danger-red:#dc2626;--success-green:#16a34a}
    body{font-family:Inter,system-ui,Arial,Helvetica,sans-serif;background:#001f3f;color:#fff}
    .app{max-width:1200px;margin:18px auto;padding:12px}
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
    .search-area{display:flex;gap:8px;align-items:center}
    .msg-cell{white-space:normal;color:#4da6ff;font-weight:500}
    footer{color:#7a9cc6;font-weight:500;padding:12px 0;border-top:2px solid #003d82;margin-top:24px}
    .stat-card{background:linear-gradient(135deg,#0d9488 0%,#10b981 100%);padding:20px;border-radius:8px;text-align:center;margin-bottom:12px}
    .stat-card h4{color:white;font-size:28px;margin:0}
    .stat-card p{color:#d1fae5;margin:5px 0 0 0}
  </style>
</head>
<body>
  <div class="app container">
    <header>
      <div>
        <h3>👤 Student Dashboard</h3>
        <div class="small-muted">View your attendance and participation records</div>
      </div>
      <div class="d-flex gap-2">
        <button id="btn-submit-justification" class="btn btn-outline-warning btn-sm">Submit Justification</button>
        <button id="btn-logout" class="btn btn-outline-danger btn-sm">Logout</button>
      </div>
    </header>

    <main>
      <!-- Statistics -->
      <div class="row mb-3">
        <div class="col-md-3">
          <div class="stat-card">
            <h4 id="stat-absences">0</h4>
            <p>Total Absences</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card" style="background:linear-gradient(135deg,#2563eb 0%,#3b82f6 100%)">
            <h4 id="stat-participations">0</h4>
            <p>Participations</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card" style="background:linear-gradient(135deg,#9333ea 0%,#a855f7 100%)">
            <h4 id="stat-attendance">0%</h4>
            <p>Attendance Rate</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card" style="background:linear-gradient(135deg,#f97316 0%,#fb923c 100%)">
            <h4 id="stat-status">Good</h4>
            <p>Status</p>
          </div>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="card p-3 mb-3">
        <h6>Your Attendance Record</h6>
        <div class="table-responsive">
          <table id="attendance-main-table" class="table table-bordered table-sm align-middle">
            <thead class="table-light">
              <tr>
                <th colspan="2" class="text-center">Sessions</th>
                <th colspan="6" class="text-center">Attendance (P = Present, Pa = Participated)</th>
                <th rowspan="2" class="text-center">Status</th>
              </tr>
              <tr class="table-secondary text-center">
                <th>Session</th>
                <th>Date</th>
                <th>S1</th><th>S2</th><th>S3</th><th>S4</th><th>S5</th><th>S6</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- Justifications Section -->
      <div class="card p-3 mb-3">
        <h6>My Justifications</h6>
        <div id="justifications-list" class="mb-3"></div>
        <button id="btn-submit-just-form" class="btn btn-primary btn-sm">Submit New Justification</button>
      </div>

      <!-- Chart -->
      <div class="card p-3 mb-3">
        <h6>Attendance Chart</h6>
        <canvas id="student-chart" height="100"></canvas>
      </div>
    </main>

    <footer class="text-center small-muted mt-2">Student Attendance Management System</footer>
  </div>

  <!-- Justification Modal -->
  <div class="modal fade" id="justificationModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content" style="background:#0a2e5c;color:white;border:1px solid #003d82">
        <div class="modal-header" style="border-bottom:1px solid #003d82">
          <h5 class="modal-title">Submit Justification</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="justification-form">
            <div class="mb-3">
              <label class="form-label">Date of Absence</label>
              <input type="date" class="form-control" id="just-date" style="background:#003d82;color:white;border:1px solid #4da6ff" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Reason</label>
              <textarea class="form-control" id="just-reason" rows="3" style="background:#003d82;color:white;border:1px solid #4da6ff" placeholder="Explain your absence..." required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Upload Document (Optional)</label>
              <input type="file" class="form-control" id="just-file" style="background:#003d82;color:white;border:1px solid #4da6ff">
              <small class="text-info">Max 5MB: PDF, JPG, PNG</small>
            </div>
            <div id="just-msg" class="small mb-2"></div>
            <button type="submit" class="btn btn-primary btn-sm">Submit Justification</button>
          </form>
        </div>
      </div>
    </div>
  </div>

<script>
const sessionCount = 6;

function initPage() {
  loadStudentData();
  attachTableEvents();
  updateStatistics();
  renderChart();
  loadJustifications();
}

function loadStudentData() {
  const tbody = $('#attendance-main-table tbody').empty();
  
  // Sample student data
  const sessions = [
    {id:1, date:'2025-01-15'},
    {id:2, date:'2025-01-22'},
    {id:3, date:'2025-01-29'},
    {id:4, date:'2025-02-05'},
    {id:5, date:'2025-02-12'},
    {id:6, date:'2025-02-19'}
  ];

  sessions.forEach(s => {
    const tr = $('<tr>').attr('data-session', s.id);
    tr.append($('<td>').text('Session ' + s.id));
    tr.append($('<td>').text(s.date));
    
    for(let i=0;i<6;i++) {
      const present = Math.random() > 0.3;
      const participated = Math.random() > 0.5;
      tr.append($('<td class="text-center">').text(present ? '✓' : '✗').css('color', present ? '#90EE90' : '#FF6B6B'));
      tr.append($('<td class="text-center">').text(participated ? '✓' : '✗').css('color', participated ? '#4da6ff' : '#999'));
    }
    
    tr.append($('<td class="text-center">').text('Present'));
    tbody.append(tr);
  });
}

function updateStatistics() {
  const absences = Math.floor(Math.random() * 3);
  const participations = Math.floor(Math.random() * 15) + 8;
  const rate = Math.round((sessionCount - absences) / sessionCount * 100);
  const status = absences === 0 ? 'Excellent' : absences <= 2 ? 'Good' : 'Fair';
  
  $('#stat-absences').text(absences);
  $('#stat-participations').text(participations);
  $('#stat-attendance').text(rate + '%');
  $('#stat-status').text(status);
}

function renderChart() {
  const ctx = document.getElementById('student-chart').getContext('2d');
  const labels = ['Session 1', 'Session 2', 'Session 3', 'Session 4', 'Session 5', 'Session 6'];
  const presents = [1, 1, 0, 1, 1, 1];
  const participations = [1, 0, 1, 1, 1, 0];
  
  if(window.studentChart) window.studentChart.destroy();
  
  window.studentChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        { label: 'Present', data: presents, backgroundColor: '#90EE90', borderColor: '#1a4d1a', borderWidth: 1 },
        { label: 'Participated', data: participations, backgroundColor: '#4da6ff', borderColor: '#002d5c', borderWidth: 1 }
      ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, max: 1, ticks: {display: false} } } }
  });
}

function loadJustifications() {
  const list = $('#justifications-list').empty();

  $.getJSON('api/justifications.php?action=student').done(function(resp){
    if(!resp || !resp.success || !resp.justifications || resp.justifications.length === 0) {
      list.html('<p class="text-muted">No justifications submitted yet.</p>');
      return;
    }

    resp.justifications.forEach(j => {
      const statusColor = j.status === 'approved' ? '#90EE90' : j.status === 'rejected' ? '#ff6b6b' : '#FFD700';
      const badgeColor = j.status === 'approved' ? '#10b981' : j.status === 'rejected' ? '#dc2626' : '#f59e0b';
      const fileLink = j.file_path ? `<a href="api/justifications.php?action=download&file_id=${j.id}" class="btn btn-sm btn-outline-light">Download</a>` : '';
      const card = $(
        '<div class="card p-2 mb-2" style="border-left:3px solid ' + statusColor + '">' +
        '<div class="d-flex justify-content-between">' +
        '<div><strong>' + (j.submitted_at || j.date || '') + '</strong> - ' + $('<div>').text(j.justification_text).html() + '</div>' +
        '<div>' + fileLink + ' <span class="badge" style="background:' + badgeColor + ';margin-left:8px">' + j.status + '</span></div>' +
        '</div></div>'
      );
      list.append(card);
    });
  }).fail(function(xhr){
    list.html('<p class="text-danger">Failed to load justifications</p>');
  });
}

function attachTableEvents() {}

$(function(){
  initPage();
  
  $('#btn-submit-just-form').on('click', function(){
    $('#justificationModal').modal('show');
  });
  
  $('#justification-form').on('submit', function(e){
    e.preventDefault();
    const date = $('#just-date').val();
    const reason = $('#just-reason').val();
    const fileInput = $('#just-file')[0];

    if(!date || !reason) { alert('Please fill all required fields'); return; }

    const formData = new FormData();
    formData.append('session_id', date);
    formData.append('justification_text', reason);
    if (fileInput.files && fileInput.files[0]) { formData.append('file', fileInput.files[0]); }

    $('#just-msg').html('<span style="color:#ffc107">Uploading...</span>');

    $.ajax({ url: 'api/justifications.php?action=submit', method: 'POST', data: formData, processData: false, contentType: false,
      success: function(resp) {
        if (resp && resp.success) {
          $('#just-msg').html('<span style="color:#90EE90">✓ Justification submitted successfully</span>');
          setTimeout(() => { const modalEl = document.getElementById('justificationModal'); const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); modal.hide(); $('#justification-form')[0].reset(); loadJustifications(); }, 900);
        } else { const err = resp && resp.error ? resp.error : 'Unknown error'; $('#just-msg').html('<span style="color:#ff6b6b">Error: ' + err + '</span>'); }
      }, error: function(xhr) { let msg = 'Failed to submit justification'; try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {} $('#just-msg').html('<span style="color:#ff6b6b">Error: ' + msg + '</span>'); }
    });
  });
  
  $('#btn-submit-justification').on('click', function(){ $('#justificationModal').modal('show'); });
  
  $('#btn-logout').on('click', function(){ window.location.href = 'login.php'; });
});
</script>

</body>
</html>
