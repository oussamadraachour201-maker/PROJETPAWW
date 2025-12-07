<?php
require_once __DIR__ . '/db_connect.php';

try {
    $db = get_db_connection();
    $stmt = $db->query('SELECT id, fullname, matricule, group_id FROM students ORDER BY fullname ASC');
    $students = $stmt->fetchAll();
} catch (Exception $e) {
    die('DB error: ' . htmlspecialchars($e->getMessage()));
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Students</title></head><body>
<h2>Students</h2>
<p><a href="add_student.php">Add New Student</a></p>
<?php if (empty($students)): ?>
  <p>No students found.</p>
<?php else: ?>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr><th>ID</th><th>Full Name</th><th>Matricule</th><th>Group</th><th>Actions</th></tr>
    <?php foreach ($students as $s): ?>
      <tr>
        <td><?php echo htmlspecialchars($s['id']); ?></td>
        <td><?php echo htmlspecialchars($s['fullname']); ?></td>
        <td><?php echo htmlspecialchars($s['matricule']); ?></td>
        <td><?php echo htmlspecialchars($s['group_id']); ?></td>
        <td>
          <a href="update_student.php?id=<?php echo urlencode($s['id']); ?>">Edit</a> |
          <a href="delete_student.php?id=<?php echo urlencode($s['id']); ?>">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>
</body></html>
