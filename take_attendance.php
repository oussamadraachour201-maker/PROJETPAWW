<?php
$studentsFile = __DIR__ . '/students.json';
$students = [];
if (file_exists($studentsFile)) {
    $students = json_decode(file_get_contents($studentsFile), true) ?: [];
}

$today = date('Y-m-d');
$attendanceFile = __DIR__ . '/attendance_' . $today . '.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prevent duplicate
    if (file_exists($attendanceFile)) {
        echo '<p>Attendance for today has already been taken.</p>';
        echo '<p><a href="' . basename(__FILE__) . '">Back</a></p>';
        exit;
    }

    $attendance = [];
    foreach ($students as $s) {
        $id = $s['student_id'] ?? '';
        $key = 'status_' . $id;
        $status = isset($_POST[$key]) && $_POST[$key] === 'present' ? 'present' : 'absent';
        $attendance[] = ['student_id' => $id, 'status' => $status];
    }

    if (file_put_contents($attendanceFile, json_encode($attendance, JSON_PRETTY_PRINT))) {
        echo '<p>Attendance saved to ' . htmlspecialchars(basename($attendanceFile)) . '</p>';
        echo '<p><a href="take_attendance.php">Back</a></p>';
        exit;
    } else {
        echo '<p style="color:red">Failed to save attendance.</p>';
    }
}

?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Take Attendance</title></head><body>
<h2>Take Attendance (<?php echo htmlspecialchars($today); ?>)</h2>
<?php if (file_exists($attendanceFile)): ?>
  <p>Attendance for today has already been taken.</p>
  <p><a href="<?php echo htmlspecialchars(basename($attendanceFile)); ?>">View today's attendance</a></p>
<?php else: ?>
  <form method="post">
    <table border="1" cellpadding="6" cellspacing="0">
      <tr><th>Student ID</th><th>Name</th><th>Status</th></tr>
      <?php foreach ($students as $s): $id = $s['student_id'] ?? ''; ?>
        <tr>
          <td><?php echo htmlspecialchars($id); ?></td>
          <td><?php echo htmlspecialchars($s['name'] ?? ''); ?></td>
          <td>
            <label><input type="radio" name="status_<?php echo htmlspecialchars($id); ?>" value="present" checked> Present</label>
            <label><input type="radio" name="status_<?php echo htmlspecialchars($id); ?>" value="absent"> Absent</label>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
    <p><button type="submit">Save Attendance</button></p>
  </form>
<?php endif; ?>
<p><a href="list_students.php">Manage Students</a></p>
</body></html>
