<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['nama']) || empty($data['username']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nama, username, dan password wajib diisi']);
    exit;
}

$nama = htmlspecialchars(trim($data['nama']));
$username = htmlspecialchars(trim($data['username']));
$password = $data['password'];
$no_hp = !empty($data['no_hp']) ? htmlspecialchars(trim($data['no_hp'])) : null;

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password minimal 6 karakter']);
    exit;
}

$conn = getConnection();

$check = $conn->prepare('SELECT id FROM users WHERE username = ?');
$check->bind_param('s', $username);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username sudah terdaftar']);
    $conn->close();
    exit;
}

$hashed = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare('INSERT INTO users (nama, username, password, no_hp, role) VALUES (?, ?, ?, ?, "user")');
$stmt->bind_param('ssss', $nama, $username, $hashed, $no_hp);

if ($stmt->execute()) {
    $new_id = $conn->insert_id;
    echo json_encode([
        'status' => 'success',
        'message' => 'Registrasi berhasil',
        'data' => [
            'id' => $new_id,
            'nama' => $nama,
            'username' => $username
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Registrasi gagal']);
}

$conn->close();
?>