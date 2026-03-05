<?php
require_once '../config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['username']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username dan password wajib diisi']);
    exit;
}

$username = htmlspecialchars(trim($data['username']));
$password = $data['password'];

$conn = getConnection();

$stmt = $conn->prepare('SELECT id, nama, username, password, role, no_hp FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Username atau password salah']);
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Username atau password salah']);
    $conn->close();
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_nama'] = $user['nama'];
$_SESSION['user_username'] = $user['username'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['logged_in'] = true;

echo json_encode([
    'status' => 'success',
    'message' => 'Login berhasil',
    'data' => [
        'id' => $user['id'],
        'nama' => $user['nama'],
        'username' => $user['username'],
        'role' => $user['role']
    ]
]);

$conn->close();
?>