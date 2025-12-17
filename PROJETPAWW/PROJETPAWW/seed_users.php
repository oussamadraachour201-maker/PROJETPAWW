<?php
// seed_users.php - create default users if none exist
require_once __DIR__ . '/db_connect.php';

try {
    $db = get_db_connection();

    // Check if users already exist
    $check = $db->prepare('SELECT COUNT(*) FROM users');
    $check->execute();
    $count = $check->fetchColumn();

    if ($count > 0) {
        echo "Users already exist in the database.\n";
        exit;
    }

    // Create sample users
    $users = [
        [
            'user_id' => 'admin',
            'first_name' => 'Site',
            'last_name' => 'Admin',
            'email' => 'admin@univ.dz',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ],
        [
            'user_id' => 'prof001',
            'first_name' => 'John',
            'last_name' => 'Professor',
            'email' => 'john@univ.dz',
            'password' => password_hash('prof123', PASSWORD_DEFAULT),
            'role' => 'professor'
        ],
        [
            'user_id' => 'stud001',
            'first_name' => 'Alice',
            'last_name' => 'Student',
            'email' => 'alice@univ.dz',
            'password' => password_hash('stud123', PASSWORD_DEFAULT),
            'role' => 'student'
        ]
    ];

    $insert = $db->prepare('INSERT INTO users (user_id, first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?, ?)');

    foreach ($users as $user) {
        $insert->execute([
            $user['user_id'],
            $user['first_name'],
            $user['last_name'],
            $user['email'],
            $user['password'],
            $user['role']
        ]);
    }

    echo "Sample users created:\n";
    echo "- Admin: admin / admin123\n";
    echo "- Professor: prof001 / prof123\n";
    echo "- Student: stud001 / stud123\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
