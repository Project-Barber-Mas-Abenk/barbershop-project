<?php
/**
 * Barbershop API Documentation
 * Backend API Index
 */

header('Content-Type: application/json');

echo json_encode([
    'status' => 'success',
    'message' => 'Barbershop API v1.0',
    'documentation' => [
        'base_url' => '/api',
        'authentication' => 'Session-based with HTTP-only cookies',
        'content_type' => 'application/json'
    ],
    'endpoints' => [
        'auth' => [
            'POST /auth/login.php' => 'Login admin/user',
            'POST /auth/register.php' => 'Register user baru',
            'POST /auth/logout.php' => 'Logout',
            'GET /auth/check_session.php' => 'Cek status login',
            'POST /auth/google_login.php' => 'Login dengan Google OAuth',
            'POST /auth/forgot_password.php' => 'Request OTP reset password',
            'POST /auth/reset_password.php' => 'Reset password dengan OTP'
        ],
        'user' => [
            'GET /user/get_profile.php' => 'Ambil profil user',
            'PUT /user/update_profile.php' => 'Update profil user',
            'POST /user/change_password.php' => 'Ubah password'
        ],
        'booking' => [
            'POST /booking/create_booking.php' => 'Buat booking baru',
            'GET /booking/get_bookings.php' => 'List booking (role-based)',
            'PUT /booking/update_status.php' => 'Update status booking (admin)',
            'PUT /booking/cancel_booking.php' => 'Cancel booking',
            'PUT /booking/reschedule.php' => 'Reschedule booking'
        ],
        'payment' => [
            'GET /payment/get_payment.php' => 'Detail pembayaran',
            'PUT /payment/update_payment.php' => 'Update status pembayaran (admin)'
        ],
        'queue' => [
            'GET /queue/get_queue.php' => 'List antrian harian'
        ],
        'barber' => [
            'GET /barber/get_barbers.php' => 'List semua barber',
            'POST /barber/create_barber.php' => 'Tambah barber (admin)',
            'PUT /barber/update_barber.php' => 'Update barber (admin)',
            'DELETE /barber/delete_barber.php' => 'Hapus barber (admin)'
        ],
        'layanan' => [
            'GET /layanan/get_layanan.php' => 'List semua layanan',
            'POST /layanan/create_layanan.php' => 'Tambah layanan (admin)',
            'PUT /layanan/update_layanan.php' => 'Update layanan (admin)',
            'DELETE /layanan/delete_layanan.php' => 'Hapus layanan (admin)'
        ],
        'kuota' => [
            'GET /kuota/get_kuota.php' => 'Cek kuota tanggal',
            'GET /kuota/get_kuota_range.php' => 'Cek kuota range tanggal',
            'PUT /kuota/update_kuota.php' => 'Update kuota harian (admin)'
        ],
        'dashboard' => [
            'GET /dashboard/stats.php' => 'Statistik dashboard'
        ],
        'admin' => [
            'GET /admin/get_users.php' => 'List semua user (admin)',
            'PUT /admin/update_user.php' => 'Update user (admin)',
            'DELETE /admin/delete_user.php' => 'Hapus user (admin)'
        ],
        'report' => [
            'GET /report/daily_report.php' => 'Report harian (admin)',
            'GET /report/monthly_report.php' => 'Report bulanan (admin)'
        ]
    ],
    'security_features' => [
        'Prepared Statements' => 'Semua query SQL menggunakan prepared statements',
        'Password Hashing' => 'bcrypt untuk password',
        'Session Security' => 'Regenerate ID, timeout, HTTP-only',
        'Rate Limiting' => '5x login, 10x register, 5x forgot password',
        'Input Validation' => 'FILTER_VALIDATE, htmlspecialchars',
        'Role-Based Access' => 'Admin vs User',
        'H-1 Validation' => 'Booking minimal 1 hari sebelum',
        'Transaction Safety' => 'begin_transaction, rollback'
    ]
], JSON_PRETTY_PRINT);
?>
