async function initBookingPage(isAdmin) {
    if (!isAdmin) {
        await loadLayananOptions();
        setupBookingForm();
    }
    await loadBookingList(isAdmin);
}

async function loadLayananOptions() {
    try {
        const response = await getLayanan();
        if (response.status === 'success') {
            const select = document.getElementById('layananSelect');
            response.data.forEach(layanan => {
                const option = document.createElement('option');
                option.value = layanan.id;
                option.textContent = `${layanan.nama} - Rp ${parseInt(layanan.harga).toLocaleString('id-ID')}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading layanan:', error);
    }
}

function setupBookingForm() {
    const form = document.getElementById('bookingForm');
    const msgDiv = document.getElementById('bookingMsg');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        const data = {
            nama: formData.get('nama'),
            no_hp: formData.get('no_hp'),
            layanan_id: parseInt(formData.get('layanan_id')),
            tanggal: formData.get('tanggal'),
            jam: formData.get('jam'),
            metode_bayar: formData.get('metode_bayar')
        };

        try {
            const result = await createBooking(data);
            if (result.status === 'success') {
                msgDiv.innerHTML = `<p style="color: green;">Booking berhasil! Nomor antrian: ${result.data.nomor_antrian}</p>`;
                form.reset();
                await loadBookingList(false);
            } else {
                msgDiv.innerHTML = `<p style="color: red;">${result.message}</p>`;
            }
        } catch (error) {
            msgDiv.innerHTML = `<p style="color: red;">Terjadi kesalahan. Coba lagi.</p>`;
        }
    });
}

async function loadBookingList(isAdmin) {
    try {
        const response = await getBookings();
        const tbody = document.querySelector('#bookingTable tbody');
        tbody.innerHTML = '';

        if (response.status !== 'success' || !response.data.length) {
            tbody.innerHTML = '<tr><td colspan="8">Tidak ada data booking</td></tr>';
            return;
        }

        response.data.forEach(booking => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${booking.pemesanan_id}</td>
                <td>${booking.nama_pelanggan}</td>
                <td>${booking.nama_layanan}</td>
                <td>${booking.tanggal}</td>
                <td>${booking.jam}</td>
                <td><span class="badge status-${booking.status_booking}">${booking.status_booking}</span></td>
                <td><span class="badge payment-${booking.status_bayar}">${booking.status_bayar}</span></td>
                ${isAdmin ? `<td>${renderAdminActions(booking)}</td>` : ''}
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error('Error loading bookings:', error);
    }
}

function renderAdminActions(booking) {
    if (booking.status_booking === 'selesai' || booking.status_booking === 'dibatalkan') {
        return '-';
    }
    
    let actions = '';
    
    if (booking.status_booking === 'menunggu') {
        actions += `<button onclick="updateStatus(${booking.pemesanan_id}, 'dikonfirmasi')">Konfirmasi</button> `;
        actions += `<button onclick="updateStatus(${booking.pemesanan_id}, 'dibatalkan')">Batal</button>`;
    } else if (booking.status_booking === 'dikonfirmasi') {
        actions += `<button onclick="updateStatus(${booking.pemesanan_id}, 'selesai')">Selesai</button> `;
    }
    
    if (booking.status_bayar === 'menunggu') {
        actions += `<button onclick="updatePaymentStatus(${booking.pemesanan_id}, 'lunas')">Bayar</button>`;
    }
    
    return actions;
}

async function updateStatus(pemesanan_id, status) {
    try {
        const result = await updateBookingStatus(pemesanan_id, status);
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Gagal update status');
    }
}

async function updatePaymentStatus(pemesanan_id, status) {
    try {
        const result = await updatePayment(pemesanan_id, status);
        if (result.status === 'success') {
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Gagal update pembayaran');
    }
}