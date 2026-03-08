<?php
require_once '../config/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

if (empty($_SESSION['logged_in'])) {
    echo json_encode([
        'status' => 'success',
        'logged_in' => false,
        'data' => null
    ]);
    exit;
}

$response = [
    'status' => 'success',
    'logged_in' => true,
    'data' => [
        'role' => $_SESSION['user_role'] ?? 'user',
        'nama' => $_SESSION['user_nama'] ?? $_SESSION['admin_nama'] ?? 'User'
    ]
];

if ($_SESSION['user_role'] === 'user') {
    $response['data']['user_id'] = $_SESSION['user_id'] ?? null;
    $response['data']['email'] = $_SESSION['user_email'] ?? null;
} else {
    $response['data']['admin_id'] = $_SESSION['admin_id'] ?? null;
}

echo json_encode($response);
?>
