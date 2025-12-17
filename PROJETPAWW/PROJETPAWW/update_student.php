<?php
require_once __DIR__ . '/db_connect.php';

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
if (!$id) die('Missing id');

try {
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT id, fullname, matricule, group_id FROM students WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $current = $stmt->fetch();
    if (!$current) die('Student not found.');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
        $group_id = isset($_POST['group_id']) ? trim($_POST['group_id']) : '';
        $errors = [];
        if ($fullname === '') $errors[] = 'Full name is required';
        if ($group_id === '') $errors[] = 'Group is required';

        if (empty($errors)) {
            $upd = $db->prepare('UPDATE students SET fullname = :fullname, group_id = :group WHERE id = :id');
            $upd->execute([':fullname' => $fullname, ':group' => $group_id, ':id' => $id]);
            echo '<p>Student updated successfully.</p>';
            echo '<p><a href="list_students.php">Back to list</a></p>';
            exit;
        }

        if (!empty($errors)) {
            echo '<p style="color:red">' . implode('<br>', array_map('htmlspecialchars', $errors)) . '</p>';
        }
    }
} catch (Exception $e) {
    die('DB error: ' . htmlspecialchars($e->getMessage()));
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Update Student</title></head><body>
<h2>Update Student <?php echo htmlspecialchars($current['matricule']); ?></h2>
<form method="post">
  <label>Full name: <input name="fullname" value="<?php echo htmlspecialchars($current['fullname']); ?>"></label><br>
  <label>Group: <input name="group_id" value="<?php echo htmlspecialchars($current['group_id']); ?>"></label><br>
  <button type="submit">Save</button>
</form>
<p><a href="list_students.php">Back to list</a></p>
</body></html>
