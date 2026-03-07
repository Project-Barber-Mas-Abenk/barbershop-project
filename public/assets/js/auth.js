const API_BASE = '../../api';

async function apiPost(endpoint, data) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return response.json();
}

async function apiGet(endpoint) {
    const response = await fetch(`${API_BASE}${endpoint}`);
    return response.json();
}

async function apiPut(endpoint, data) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return response.json();
}

async function login(email, password) {
    return apiPost('/auth/login.php', { email, password });
}

async function register(nama, email, no_hp, password) {
    return apiPost('/auth/register.php', { nama, email, no_hp, password });
}

async function logout() {
    return apiPost('/auth/logout.php', {});
}

async function googleLogin(email, nama, google_id, no_hp = '') {
    return apiPost('/auth/google_login.php', { email, nama, google_id, no_hp });
}

async function getBookings(params = {}) {
    const query = new URLSearchParams(params).toString();
    return apiGet(`/booking/get_bookings.php${query ? '?' + query : ''}`);
}

async function createBooking(data) {
    return apiPost('/booking/create_booking.php', data);
}

async function updateBookingStatus(pemesanan_id, status) {
    return apiPut('/booking/update_status.php', { pemesanan_id, status });
}

async function rescheduleBooking(pemesanan_id, tanggal_baru, jam_baru) {
    return apiPut('/booking/reschedule.php', { pemesanan_id, tanggal_baru, jam_baru });
}

async function getLayanan(id = null) {
    if (id) {
        return apiGet(`/layanan/get_layanan.php?id=${id}`);
    }
    return apiGet('/layanan/get_layanan.php');
}

async function getQueue(tanggal = null) {
    const query = tanggal ? `?tanggal=${tanggal}` : '';
    return apiGet(`/queue/get_queue.php${query}`);
}

async function getPayment(pemesanan_id) {
    return apiGet(`/payment/get_payment.php?pemesanan_id=${pemesanan_id}`);
}

async function updatePayment(pemesanan_id, status) {
    return apiPut('/payment/update_payment.php', { pemesanan_id, status });
}

function formatRupiah(angka) {
    return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
}

function formatTanggal(tanggal) {
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return new Date(tanggal).toLocaleDateString('id-ID', options);
}

function showError(elementId, message) {
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = message;
        el.style.display = 'block';
    }
}

function hideError(elementId) {
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = '';
        el.style.display = 'none';
    }
}

function setLoading(buttonId, isLoading, text = 'Submit') {
    const btn = document.getElementById(buttonId);
    if (btn) {
        btn.disabled = isLoading;
        btn.textContent = isLoading ? 'Memproses...' : text;
    }
}
