<?php
// seed_users.php - create a default admin user if none exists
require_once __DIR__ . '/db_connect.php';

try {
    $db = get_db_connection();

    // Ensure roles exist
    $roles = ['admin', 'professor', 'student'];
    $ins = $db->prepare('INSERT IGNORE INTO roles (name, description) VALUES (:name, :desc)');
    foreach ($roles as $r) {
        $ins->execute([':name' => $r, ':desc' => ucfirst($r)]);
    }

    // Create admin user if not exists
    $check = $db->prepare('SELECT id FROM users WHERE username = :u LIMIT 1');
    $check->execute([':u' => 'admin']);
    $row = $check->fetch();
    if ($row) {
        echo "Admin user already exists (id: {$row['id']}).\n";
        exit;
    }

    // find admin role id
    $rstmt = $db->prepare('SELECT id FROM roles WHERE name = :n LIMIT 1');
    $rstmt->execute([':n' => 'admin']);
    $rid = $rstmt->fetchColumn();

    $pw = password_hash('admin123', PASSWORD_BCRYPT);
    $insert = $db->prepare('INSERT INTO users (username, password_hash, email, role_id, fullname) VALUES (:u, :p, :e, :r, :f)');
    $insert->execute([':u' => 'admin', ':p' => $pw, ':e' => 'admin@univ.dz', ':r' => $rid, ':f' => 'Site Admin']);

    echo "Admin user created: username=admin password=admin123\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
