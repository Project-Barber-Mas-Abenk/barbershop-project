<?php
// method : POST
// URL : /api/auth/login.php
// Body JSON:
// username - username admin
// password - password admin (plain text, diverifikasi dengan hash di DB)

require_once '../config/config.php';

// session_start() harus dipanggil sebelum apapun di output
// ini yang bikin status login 'diinget' sama server
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

// cari admin berdasarkan username dulu
// jangan langsung filter by password di sql
// karena password di db sudah berupa hash
$stmt = $conn->prepare('SELECT id, nama, password FROM admin WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // username ga ditemuin. responnya sengaja sama
    // dengan "password salah" supaya attacker ga bisa tahu mana yang bener
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Username atau password salah']);
    $conn->close();
    exit;
}

$admin = $result->fetch_assoc();

// verifikasi password pakai password_verify()
// fungsi ini ngecocokin plain text dengan hash bcrypt
// jangan pakai MD5 atau SHA1
if (!password_verify($password, $admin['password'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Username atau password salah']);
    $conn->close();
    exit;
}

// login berhasil. simpan info admin ke session
// $_SESSION ini akan tersedia di semua request
// selama session masih aktif (browser belum tutup)
$_SESSION['admin_id']   = $admin['id'];
$_SESSION['admin_nama'] = $admin['nama'];
$_SESSION['logged_in']  = true; 

echo json_encode([
    'status'  => 'success',
    'message' => 'Login berhasil',
    'data'    => ['nama' => $admin['nama']]
]);

$conn->close();
?>