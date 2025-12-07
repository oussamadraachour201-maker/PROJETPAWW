<?php
// save_settings.php - simple settings store (JSON file)
$settingsFile = __DIR__ . '/settings.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $data = [];
    if (file_exists($settingsFile)) {
        $data = json_decode(file_get_contents($settingsFile), true) ?: [];
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'settings' => $data]);
    exit;
}

// POST to save
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data || !is_array($data)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid payload']);
    exit;
}

// Basic allowed keys
$allowed = ['site_title', 'admin_email'];
$out = [];
foreach ($allowed as $k) {
    if (isset($data[$k])) $out[$k] = $data[$k];
}

// merge with existing
$current = [];
if (file_exists($settingsFile)) $current = json_decode(file_get_contents($settingsFile), true) ?: [];
$merged = array_merge($current, $out);

if (file_put_contents($settingsFile, json_encode($merged, JSON_PRETTY_PRINT))) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Failed to write file']);
}
