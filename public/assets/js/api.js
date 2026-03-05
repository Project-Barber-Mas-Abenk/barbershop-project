const API_BASE = '../../api';

async function fetchData(url) {
    const response = await fetch(url);
    return response.json();
}

async function postData(url, data) {
    const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return response.json();
}

async function putData(url, data) {
    const response = await fetch(url, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    return response.json();
}

async function getLayanan() {
    return fetchData(`${API_BASE}/layanan/get_layanan.php`);
}

async function getBookings() {
    return fetchData(`${API_BASE}/booking/get_bookings.php`);
}

async function createBooking(data) {
    return postData(`${API_BASE}/booking/create_booking.php`, data);
}

async function updateBookingStatus(pemesanan_id, status) {
    return putData(`${API_BASE}/booking/update_status.php`, { pemesanan_id, status });
}

async function rescheduleBooking(pemesanan_id, tanggal_baru, jam_baru) {
    return putData(`${API_BASE}/booking/reschedule.php`, { pemesanan_id, tanggal_baru, jam_baru });
}

async function getPayment(pemesanan_id) {
    return fetchData(`${API_BASE}/payment/get_payment.php?pemesanan_id=${pemesanan_id}`);
}

async function updatePayment(pemesanan_id, status) {
    return putData(`${API_BASE}/payment/update_payment.php`, { pemesanan_id, status });
}

async function getQueue(tanggal = '') {
    const url = tanggal 
        ? `${API_BASE}/queue/get_queue.php?tanggal=${tanggal}`
        : `${API_BASE}/queue/get_queue.php`;
    return fetchData(url);
}