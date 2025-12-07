<?php
// add_student.php - inserts a student into the `students` DB table
// Fields: matricule, fullname, group_id
require_once __DIR__ . '/db_connect.php';

$error = null;
$old = ['matricule' => '', 'fullname' => '', 'group_id' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['matricule'] = isset($_POST['matricule']) ? trim($_POST['matricule']) : '';
    $old['fullname'] = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $old['group_id'] = isset($_POST['group_id']) ? trim($_POST['group_id']) : '';

    $errors = [];
    if ($old['matricule'] === '') $errors[] = 'Matricule is required';
    if ($old['fullname'] === '') $errors[] = 'Full name is required';
    if ($old['group_id'] === '') $errors[] = 'Group is required';

    if (empty($errors)) {
        try {
            $db = get_db_connection();
            // Check duplicate matricule
            $stmt = $db->prepare('SELECT id FROM students WHERE matricule = :m LIMIT 1');
            $stmt->execute([':m' => $old['matricule']]);
            if ($stmt->fetch()) {
                $errors[] = 'A student with that matricule already exists';
            } else {
                $ins = $db->prepare('INSERT INTO students (fullname, matricule, group_id) VALUES (:fullname, :matricule, :group_id)');
                $ins->execute([
                    ':fullname' => $old['fullname'],
                    ':matricule' => $old['matricule'],
                    ':group_id' => $old['group_id']
                ]);
                echo '<p>Student added successfully.</p>';
                echo '<p><a href="list_students.php">Back to list</a></p>';
                exit;
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $error = implode('<br>', array_map('htmlspecialchars', $errors));
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Add Student</title></head><body>
<h2>Add Student</h2>
<?php if ($error): ?><p style="color:red"><?php echo $error; ?></p><?php endif; ?>
<form method="post" action="">
  <label>Matricule: <input name="matricule" value="<?php echo htmlspecialchars($old['matricule']); ?>"></label><br>
  <label>Full name: <input name="fullname" value="<?php echo htmlspecialchars($old['fullname']); ?>"></label><br>
  <label>Group: <input name="group_id" value="<?php echo htmlspecialchars($old['group_id']); ?>"></label><br>
  <button type="submit">Add Student</button>
</form>
<p><a href="list_students.php">View students</a></p>
</body></html>
