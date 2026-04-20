<?php
/**
 * Daily Report API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat mengakses
 * [SECURITY] Report harian booking dan income
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// AUTHENTICATION & AUTHORIZATION
// ============================================================================

if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

$role = $_SESSION['user_role'] ?? '';
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

// [SECURITY] Validasi tanggal
$tanggal = isset($_GET['tanggal']) ? htmlspecialchars(trim($_GET['tanggal']), ENT_QUOTES, 'UTF-8') : date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format tanggal tidak valid']);
    exit;
}

$conn = getConnection();

// ============================================================================
// DAILY STATISTICS
// ============================================================================

// Total booking hari ini
$stmt1 = $conn->prepare('SELECT COUNT(*) as total FROM pemesanan WHERE tanggal = ?');
$stmt1->bind_param('s', $tanggal);
$stmt1->execute();
$total_booking = $stmt1->get_result()->fetch_assoc()['total'];
$stmt1->close();

// Booking by status
$stmt2 = $conn->prepare('
    SELECT status, COUNT(*) as count 
    FROM pemesanan 
    WHERE tanggal = ? 
    GROUP BY status
');
$stmt2->bind_param('s', $tanggal);
$stmt2->execute();
$result2 = $stmt2->get_result();
$status_breakdown = [];
while ($row = $result2->fetch_assoc()) {
    $status_breakdown[$row['status']] = $row['count'];
}
$stmt2->close();

// Total income hari ini
$stmt3 = $conn->prepare('
    SELECT SUM(jumlah) as total 
    FROM pembayaran p
    JOIN pemesanan pe ON p.pemesanan_id = pe.id
    WHERE pe.tanggal = ? AND p.status = "lunas"
');
$stmt3->bind_param('s', $tanggal);
$stmt3->execute();
$total_income = $stmt3->get_result()->fetch_assoc()['total'] ?? 0;
$stmt3->close();

// Payment method breakdown
$stmt4 = $conn->prepare('
    SELECT p.metode, COUNT(*) as count, SUM(p.jumlah) as total
    FROM pembayaran p
    JOIN pemesanan pe ON p.pemesanan_id = pe.id
    WHERE pe.tanggal = ? AND p.status = "lunas"
    GROUP BY p.metode
');
$stmt4->bind_param('s', $tanggal);
$stmt4->execute();
$result4 = $stmt4->get_result();
$payment_breakdown = [];
while ($row = $result4->fetch_assoc()) {
    $payment_breakdown[] = [
        'metode' => $row['metode'],
        'count' => $row['count'],
        'total' => floatval($row['total'])
    ];
}
$stmt4->close();

// Top layanan hari ini
$stmt5 = $conn->prepare('
    SELECT l.nama, COUNT(*) as count
    FROM pemesanan p
    JOIN layanan l ON p.layanan_id = l.id
    WHERE p.tanggal = ?
    GROUP BY l.id
    ORDER BY count DESC
    LIMIT 5
');
$stmt5->bind_param('s', $tanggal);
$stmt5->execute();
$result5 = $stmt5->get_result();
$top_layanan = [];
while ($row = $result5->fetch_assoc()) {
    $top_layanan[] = $row;
}
$stmt5->close();

echo json_encode([
    'status' => 'success',
    'data' => [
        'tanggal' => $tanggal,
        'total_booking' => $total_booking,
        'status_breakdown' => $status_breakdown,
        'total_income' => floatval($total_income),
        'payment_breakdown' => $payment_breakdown,
        'top_layanan' => $top_layanan
    ]
]);

$conn->close();
?>
