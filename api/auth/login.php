<?php
require_once '../config/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email dan password wajib diisi']);
    exit;
}

$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$password = $data['password'];

$conn = getConnection();

$stmt = $conn->prepare('SELECT id, nama, password FROM admin WHERE username = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nama'] = $admin['nama'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['logged_in'] = true;
        session_regenerate_id(true);

        echo json_encode([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'nama' => $admin['nama'],
                'role' => 'admin'
            ]
        ]);
        $conn->close();
        exit;
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Username atau password salah']);
        $conn->close();
        exit;
    }
}

$stmt2 = $conn->prepare('SELECT id, nama, email, password, role FROM users WHERE email = ?');
$stmt2->bind_param('s', $email);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Email atau password salah']);
    $conn->close();
    exit;
}

$user = $result2->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Email atau password salah']);
    $conn->close();
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_nama'] = $user['nama'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['logged_in'] = true;
session_regenerate_id(true);

echo json_encode([
    'status' => 'success',
    'message' => 'Login berhasil',
    'data' => [
        'user_id' => $user['id'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'role' => $user['role']
    ]
]);

$conn->close();
?>
