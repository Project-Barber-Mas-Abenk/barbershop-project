<?php
/**
 * Dashboard Stats API
 * Barbershop Project - Backend
 * 
 * [SECURITY] Endpoint ini memerlukan autentikasi
 * [SECURITY] Semua query menggunakan prepared statements untuk mencegah SQL Injection
 */

require_once '../config/config.php';

session_start();

// ============================================================================
// AUTHENTICATION CHECK
// ============================================================================

// [SECURITY] Cek apakah user sudah login
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

// [SECURITY] Validasi HTTP method
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

// ============================================================================
// ADMIN STATS
// ============================================================================

if ($role === 'admin') {
    
    // [SECURITY] Booking Hari Ini - menggunakan prepared statement
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pemesanan WHERE tanggal = ?");
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $stats['today_booking'] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // [SECURITY] Booking Besok - menggunakan prepared statement
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pemesanan WHERE tanggal = ?");
    $stmt->bind_param('s', $tomorrow);
    $stmt->execute();
    $stats['tomorrow_booking'] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // [SECURITY] Pelanggan Hari Ini (Unik berdasarkan pelanggan_id) - prepared statement
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT pelanggan_id) as total FROM pemesanan WHERE tanggal = ?");
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $stats['today_customers'] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // [SECURITY] Pelanggan Minggu Ini - menggunakan prepared statement dengan date range
    $startOfWeek = date('Y-m-d', strtotime('monday this week'));
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT pelanggan_id) as total FROM pemesanan WHERE tanggal >= ?");
    $stmt->bind_param('s', $startOfWeek);
    $stmt->execute();
    $stats['weekly_customers'] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // [SECURITY] Pelanggan Bulan Ini - menggunakan prepared statement dengan date range
    $startOfMonth = date('Y-m-01');
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT pelanggan_id) as total FROM pemesanan WHERE tanggal >= ?");
    $stmt->bind_param('s', $startOfMonth);
    $stmt->execute();
    $stats['monthly_customers'] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // [SECURITY] Belum Dicatat (Status menunggu) - prepared statement
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM pemesanan WHERE status = ?');
    $status_menunggu = 'menunggu';
    $stmt->bind_param('s', $status_menunggu);
    $stmt->execute();
    $stats['unrecorded'] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // [SECURITY] Total Income - prepared statement
    $stmt = $conn->prepare('SELECT SUM(jumlah) as total FROM pembayaran WHERE status = ?');
    $status_lunas = 'lunas';
    $stmt->bind_param('s', $status_lunas);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stats['total_income'] = $row['total'] ? floatval($row['total']) : 0;
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'data' => $stats
    ]);
} else {
    // ============================================================================
    // USER STATS
    // ============================================================================
    
    // [SECURITY] Total Booking User - menggunakan prepared statement dengan user_id
    $stmt = $conn->prepare('
        SELECT COUNT(*) as total 
        FROM pemesanan p 
        JOIN pelanggan pl ON p.pelanggan_id = pl.id 
        WHERE pl.user_id = ?
    ');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stats['total_booking'] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // [SECURITY] Booking Aktif (menunggu atau dikonfirmasi) - prepared statement
    $stmt = $conn->prepare('
        SELECT COUNT(*) as total 
        FROM pemesanan p 
        JOIN pelanggan pl ON p.pelanggan_id = pl.id 
        WHERE pl.user_id = ? AND p.status IN (?, ?)
    ');
    $status_menunggu = 'menunggu';
    $status_dikonfirmasi = 'dikonfirmasi';
    $stmt->bind_param('iss', $user_id, $status_menunggu, $status_dikonfirmasi);
    $stmt->execute();
    $stats['active_booking'] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'data' => $stats
    ]);
}

$conn->close();
?>
