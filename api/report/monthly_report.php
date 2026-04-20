<?php
/**
 * Monthly Report API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Hanya admin yang dapat mengakses
 * [SECURITY] Report bulanan booking dan income
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

// [SECURITY] Validasi input
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : intval(date('m'));
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : intval(date('Y'));

if ($bulan < 1 || $bulan > 12 || $tahun < 2020 || $tahun > 2100) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Bulan atau tahun tidak valid']);
    exit;
}

$start_date = sprintf('%04d-%02d-01', $tahun, $bulan);
$end_date = date('Y-m-t', strtotime($start_date));

$conn = getConnection();

// ============================================================================
// MONTHLY STATISTICS
// ============================================================================

// Total booking bulan ini
$stmt1 = $conn->prepare('SELECT COUNT(*) as total FROM pemesanan WHERE tanggal BETWEEN ? AND ?');
$stmt1->bind_param('ss', $start_date, $end_date);
$stmt1->execute();
$total_booking = $stmt1->get_result()->fetch_assoc()['total'];
$stmt1->close();

// Total income bulan ini
$stmt2 = $conn->prepare('
    SELECT SUM(jumlah) as total 
    FROM pembayaran p
    JOIN pemesanan pe ON p.pemesanan_id = pe.id
    WHERE pe.tanggal BETWEEN ? AND ? AND p.status = "lunas"
');
$stmt2->bind_param('ss', $start_date, $end_date);
$stmt2->execute();
$total_income = $stmt2->get_result()->fetch_assoc()['total'] ?? 0;
$stmt2->close();

// Daily breakdown
$stmt3 = $conn->prepare('
    SELECT 
        tanggal,
        COUNT(*) as total_booking,
        SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as selesai,
        SUM(CASE WHEN status = "dibatalkan" THEN 1 ELSE 0 END) as dibatalkan
    FROM pemesanan 
    WHERE tanggal BETWEEN ? AND ?
    GROUP BY tanggal
    ORDER BY tanggal ASC
');
$stmt3->bind_param('ss', $start_date, $end_date);
$stmt3->execute();
$result3 = $stmt3->get_result();
$daily_stats = [];
while ($row = $result3->fetch_assoc()) {
    $daily_stats[] = $row;
}
$stmt3->close();

// Top pelanggan bulan ini
$stmt4 = $conn->prepare('
    SELECT 
        pl.nama,
        pl.no_hp,
        COUNT(*) as total_booking,
        SUM(l.harga) as total_spent
    FROM pemesanan p
    JOIN pelanggan pl ON p.pelanggan_id = pl.id
    JOIN layanan l ON p.layanan_id = l.id
    WHERE p.tanggal BETWEEN ? AND ?
    GROUP BY pl.id
    ORDER BY total_booking DESC
    LIMIT 10
');
$stmt4->bind_param('ss', $start_date, $end_date);
$stmt4->execute();
$result4 = $stmt4->get_result();
$top_pelanggan = [];
while ($row = $result4->fetch_assoc()) {
    $top_pelanggan[] = $row;
}
$stmt4->close();

echo json_encode([
    'status' => 'success',
    'data' => [
        'bulan' => $bulan,
        'tahun' => $tahun,
        'periode' => $start_date . ' s/d ' . $end_date,
        'total_booking' => $total_booking,
        'total_income' => floatval($total_income),
        'daily_stats' => $daily_stats,
        'top_pelanggan' => $top_pelanggan
    ]
]);

$conn->close();
?>
