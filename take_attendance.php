<?php
$today = date('Y-m-d');
$attendanceFile = __DIR__ . '/attendance_' . $today . '.json';

$students = [];
$usingDb = false;
// Try DB-backed students first (if DB configured)
if (file_exists(__DIR__ . '/db_connect.php')) {
    try {
        require_once __DIR__ . '/db_connect.php';
        $pdo = get_db_connection();
        $stmt = $pdo->query('SELECT id, matricule, group_name FROM students ORDER BY id');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            foreach ($rows as $r) {
                $students[] = ['student_id' => $r['id'], 'name' => $r['matricule'] ?: ('student_' . $r['id'])];
            }
            $usingDb = true;
        }
    } catch (Exception $e) {
        // silently fall back to JSON file
        $usingDb = false;
    }
}

if (!$usingDb) {
    $studentsFile = __DIR__ . '/students.json';
    if (file_exists($studentsFile)) {
        $students = json_decode(file_get_contents($studentsFile), true) ?: [];
    }
}

// If attendance file already exists (legacy), show view link
if (file_exists($attendanceFile)) {
    ?>
    <!doctype html>
    <html><head><meta charset="utf-8"><title>Take Attendance</title></head><body>
    <h2>Take Attendance (<?php echo htmlspecialchars($today); ?>)</h2>
    <p>Attendance for today has already been taken.</p>
    <p><a href="<?php echo htmlspecialchars(basename($attendanceFile)); ?>">View today's attendance</a></p>
    <p><a href="list_students.php">Manage Students</a></p>
    </body></html>
    <?php
    exit;
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Take Attendance</title>
  <style>
    table { border-collapse: collapse; }
    td, th { border: 1px solid #ccc; padding: 6px; }
    .present { background: #e6ffe6; }
  </style>
</head>
<body>
<h2>Take Attendance (<?php echo htmlspecialchars($today); ?>)</h2>

<form id="attendance-form">
  <table id="attendance-table">
    <tr><th>Student ID</th><th>Name</th><th>Status</th></tr>
    <?php foreach ($students as $s): $id = $s['student_id'] ?? ''; ?>
      <tr data-student-id="<?php echo htmlspecialchars($id); ?>">
        <td><?php echo htmlspecialchars($id); ?></td>
        <td><?php echo htmlspecialchars($s['name'] ?? ''); ?></td>
        <td>
          <select name="status_<?php echo htmlspecialchars($id); ?>">
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
            <option value="excused">Excused</option>
          </select>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <p>
    <button id="save-btn" type="button">Save Attendance (DB)</button>
    <button id="save-legacy" type="submit">Save Legacy (file)</button>
  </p>
</form>

<p><a href="list_students.php">Manage Students</a></p>

<script>
document.getElementById('save-btn').addEventListener('click', function () {
  const rows = document.querySelectorAll('#attendance-table tr[data-student-id]');
  const attendance = [];
  rows.forEach(function (r) {
    const sid = r.getAttribute('data-student-id');
    const sel = r.querySelector('select');
    const status = sel ? sel.value : 'absent';
    attendance.push({ student_id: sid, status: status });
  });

  const payload = { date: '<?php echo $today; ?>', attendance: attendance };

  fetch('api/save_attendance.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  }).then(r => r.json()).then(function (data) {
    if (data && data.success) {
      alert('Attendance saved (session id: ' + data.session_id + ')');
      location.reload();
    } else {
      alert('Save failed: ' + (data && data.error ? data.error : 'unknown'));
    }
  }).catch(function (err) {
    alert('Network error saving attendance: ' + err);
  });
});

// Legacy submit still posts to the old file-saving behavior
document.getElementById('attendance-form').addEventListener('submit', function (e) {
  // leave as normal form submit for backward-compatibility
});
</script>

</body></html>

