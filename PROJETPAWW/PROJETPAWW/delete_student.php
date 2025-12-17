<?php
require_once __DIR__ . '/db_connect.php';

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
if (!$id) die('Missing id');

try {
    $db = get_db_connection();
    $stmt = $db->prepare('SELECT id, fullname FROM students WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $s = $stmt->fetch();
    if (!$s) die('Student not found.');

    if (isset($_REQUEST['confirm']) && $_REQUEST['confirm'] == '1') {
        $del = $db->prepare('DELETE FROM students WHERE id = :id');
        $del->execute([':id' => $id]);
        echo '<p>Student deleted.</p>';
        echo '<p><a href="list_students.php">Back to list</a></p>';
        exit;
    }
} catch (Exception $e) {
    die('DB error: ' . htmlspecialchars($e->getMessage()));
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Delete Student</title></head><body>
<h2>Delete Student <?php echo htmlspecialchars($s['id']); ?></h2>
<p>Are you sure you want to delete <?php echo htmlspecialchars($s['fullname']); ?>?</p>
<p><a href="delete_student.php?id=<?php echo urlencode($id); ?>&confirm=1">Yes, delete</a> | <a href="list_students.php">Cancel</a></p>
</body></html>
