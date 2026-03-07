<?php
require_once '../config/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['nama']) || empty($data['email']) || empty($data['password']) || empty($data['no_hp'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nama, email, password, dan no_hp wajib diisi']);
    exit;
}

$nama = htmlspecialchars(trim($data['nama']));
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$password = $data['password'];
$no_hp = htmlspecialchars(trim($data['no_hp']));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password minimal 6 karakter']);
    exit;
}

$conn = getConnection();

$check = $conn->prepare('SELECT id FROM users WHERE email = ?');
$check->bind_param('s', $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Email sudah terdaftar']);
    $conn->close();
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$conn->begin_transaction();

try {
    $stmt = $conn->prepare('INSERT INTO users (email, password, nama, no_hp, role) VALUES (?, ?, ?, ?, "user")');
    $stmt->bind_param('ssss', $email, $hash, $nama, $no_hp);
    $stmt->execute();
    $user_id = $conn->insert_id;

    $stmt2 = $conn->prepare('INSERT INTO pelanggan (user_id, nama, no_hp) VALUES (?, ?, ?)');
    $stmt2->bind_param('iss', $user_id, $nama, $no_hp);
    $stmt2->execute();

    $conn->commit();

    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_nama'] = $nama;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = 'user';
    $_SESSION['logged_in'] = true;
    session_regenerate_id(true);

    echo json_encode([
        'status' => 'success',
        'message' => 'Registrasi berhasil',
        'data' => [
            'user_id' => $user_id,
            'nama' => $nama,
            'email' => $email,
            'role' => 'user'
        ]
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Registrasi gagal: ' . $e->getMessage()]);
}

$conn->close();
?>
