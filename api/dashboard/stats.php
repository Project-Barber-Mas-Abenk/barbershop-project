<?php
require_once '../config/config.php';

session_start();

if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$conn = getConnection();

$role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

$today = date('Y-m-d');
$stats = [];

if ($role === 'admin') {
    $result = $conn->query('SELECT COUNT(*) as total FROM pemesanan');
    $stats['total_booking'] = $result->fetch_assoc()['total'];

    $result = $conn->query('SELECT COUNT(*) as total FROM pemesanan WHERE status = "menunggu"');
    $stats['pending'] = $result->fetch_assoc()['total'];

    $result = $conn->query('SELECT COUNT(*) as total FROM pemesanan WHERE status = "dikonfirmasi"');
    $stats['confirmed'] = $result->fetch_assoc()['total'];

    $result = $conn->query('SELECT COUNT(*) as total FROM pemesanan WHERE status = "selesai"');
    $stats['completed'] = $result->fetch_assoc()['total'];

    $result = $conn->query('SELECT COUNT(*) as total FROM pemesanan WHERE status = "dibatalkan"');
    $stats['cancelled'] = $result->fetch_assoc()['total'];

    $result = $conn->query('SELECT SUM(jumlah) as total FROM pembayaran WHERE status = "lunas"');
    $row = $result->fetch_assoc();
    $stats['total_income'] = $row['total'] ? floatval($row['total']) : 0;

    $result = $conn->query("SELECT COUNT(*) as total FROM pemesanan WHERE tanggal = '$today'");
    $stats['today_booking'] = $result->fetch_assoc()['total'];
} else {
    $stmt = $conn->prepare('
        SELECT COUNT(*) as total 
        FROM pemesanan p 
        JOIN pelanggan pl ON p.pelanggan_id = pl.id 
        WHERE pl.user_id = ?
    ');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stats['total_booking'] = $stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare('
        SELECT COUNT(*) as total 
        FROM pemesanan p 
        JOIN pelanggan pl ON p.pelanggan_id = pl.id 
        WHERE pl.user_id = ? AND p.status = "menunggu"
    ');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stats['pending'] = $stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare('
        SELECT COUNT(*) as total 
        FROM pemesanan p 
        JOIN pelanggan pl ON p.pelanggan_id = pl.id 
        WHERE pl.user_id = ? AND p.status = "dikonfirmasi"
    ');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stats['confirmed'] = $stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare('
        SELECT COUNT(*) as total 
        FROM pemesanan p 
        JOIN pelanggan pl ON p.pelanggan_id = pl.id 
        WHERE pl.user_id = ? AND p.status = "selesai"
    ');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stats['completed'] = $stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare('
        SELECT COUNT(*) as total 
        FROM pemesanan p 
        JOIN pelanggan pl ON p.pelanggan_id = pl.id 
        WHERE pl.user_id = ? AND p.status = "dibatalkan"
    ');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stats['cancelled'] = $stmt->get_result()->fetch_assoc()['total'];

    $stmt = $conn->prepare('
        SELECT COUNT(*) as total 
        FROM pemesanan p 
        JOIN pelanggan pl ON p.pelanggan_id = pl.id 
        WHERE pl.user_id = ? AND p.tanggal = ?
    ');
    $stmt->bind_param('is', $user_id, $today);
    $stmt->execute();
    $stats['today_booking'] = $stmt->get_result()->fetch_assoc()['total'];

    $stats['total_income'] = 0;
}

echo json_encode([
    'status' => 'success',
    'data' => $stats
]);

$conn->close();
?>
