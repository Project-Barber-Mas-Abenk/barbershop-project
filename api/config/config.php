<?php
/**
 * Konfigurasi Database dan Security
 * Barbershop Project - Backend
 * 
 * [SECURITY NOTICE] File ini berisi konfigurasi sensitif
 * Pastikan file ini tidak dapat diakses langsung dari browser
 */

// ============================================================================
// DATABASE CONFIGURATION
// ============================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'barbershop_db');
define('KUOTA_HARIAN', 4);

// ============================================================================
// SECURITY CONSTANTS
// ============================================================================
define('SESSION_LIFETIME', 3600); // 1 jam
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 menit

/**
 * Get Database Connection dengan security hardening
 * 
 * @return mysqli Koneksi database yang sudah dikonfigurasi
 * @throws Exception Jika koneksi gagal
 */
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        // [SECURITY] Log error tanpa expose detail ke user
        error_log("[BARBERSHOP SECURITY] Database connection failed: " . $conn->connect_error);
        http_response_code(500);
        die(json_encode([
            'status' => 'error',
            'message' => 'Koneksi database gagal. Silakan hubungi administrator.'
        ]));
    }
    
    // Set charset ke utf8mb4 untuk mendukung unicode penuh
    $conn->set_charset('utf8mb4');
    
    // Set timezone untuk konsistensi waktu
    $conn->query("SET time_zone = '+07:00'");
    
    return $conn;
}

// ============================================================================
// SECURITY HEADERS
// ============================================================================

/**
 * Set security headers untuk melindungi dari serangan umum
 * [SECURITY] Headers ini wajib di-set di setiap response
 */
function setSecurityHeaders() {
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// ============================================================================
// CORS CONFIGURATION
// ============================================================================

/**
 * Validate Origin untuk CORS
 * [SECURITY] Hanya allow origin yang terdaftar
 * 
 * @param string $origin Origin dari request
 * @return bool True jika origin diizinkan
 */
function isAllowedOrigin($origin) {
    $allowedOrigins = [
        'http://localhost',
        'http://localhost:3000',
        'http://localhost:8080',
        'http://127.0.0.1',
        'https://barbershop-project.vercel.app', // Production domain
    ];
    
    foreach ($allowedOrigins as $allowed) {
        if (strpos($origin, $allowed) === 0) {
            return true;
        }
    }
    
    return false;
}

// Set CORS headers
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (isAllowedOrigin($origin)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    // [SECURITY] Origin tidak diizinkan - tetap set header tapi tanpa credentials
    header('Access-Control-Allow-Origin: *');
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

// Set security headers
setSecurityHeaders();

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>
