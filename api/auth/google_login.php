<?php
require_once '../config/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['email']) || empty($data['nama']) || empty($data['google_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data Google tidak lengkap']);
    exit;
}

$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$nama = htmlspecialchars(trim($data['nama']));
$google_id = htmlspecialchars(trim($data['google_id']));
$no_hp = !empty($data['no_hp']) ? htmlspecialchars(trim($data['no_hp'])) : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format email tidak valid']);
    exit;
}

$conn = getConnection();

$stmt = $conn->prepare('SELECT id, nama, email, role FROM users WHERE google_id = ? OR email = ?');
$stmt->bind_param('ss', $google_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if (empty($user['google_id'])) {
        $update = $conn->prepare('UPDATE users SET google_id = ? WHERE id = ?');
        $update->bind_param('si', $google_id, $user['id']);
        $update->execute();
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nama'] = $user['nama'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    session_regenerate_id(true);

    echo json_encode([
        'status' => 'success',
        'message' => 'Login Google berhasil',
        'data' => [
            'user_id' => $user['id'],
            'nama' => $user['nama'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
} else {
    $conn->begin_transaction();
    
    try {
        $stmt2 = $conn->prepare('INSERT INTO users (email, nama, no_hp, google_id, role) VALUES (?, ?, ?, ?, "user")');
        $stmt2->bind_param('ssss', $email, $nama, $no_hp, $google_id);
        $stmt2->execute();
        $user_id = $conn->insert_id;
        
        $stmt3 = $conn->prepare('INSERT INTO pelanggan (user_id, nama, no_hp) VALUES (?, ?, ?)');
        $stmt3->bind_param('iss', $user_id, $nama, $no_hp);
        $stmt3->execute();
        
        $conn->commit();
        
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_nama'] = $nama;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'user';
        $_SESSION['logged_in'] = true;
        session_regenerate_id(true);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Akun Google berhasil dibuat dan login',
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
        echo json_encode(['status' => 'error', 'message' => 'Gagal membuat akun: ' . $e->getMessage()]);
    }
}

$conn->close();
?>
