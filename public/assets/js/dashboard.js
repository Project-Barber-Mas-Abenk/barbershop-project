async function loadDashboardData(isAdmin) {
    try {
        const response = await getBookings();
        if (response.status !== 'success') return;

        const bookings = response.data || [];
        
        document.getElementById('totalBooking').textContent = bookings.length;
        document.getElementById('pendingBooking').textContent = bookings.filter(b => b.status_booking === 'menunggu').length;
        document.getElementById('confirmedBooking').textContent = bookings.filter(b => b.status_booking === 'dikonfirmasi').length;
        
        if (isAdmin) {
            document.getElementById('cancelledBooking').textContent = bookings.filter(b => b.status_booking === 'dibatalkan').length;
            const revenue = bookings
                .filter(b => b.status_bayar === 'lunas')
                .reduce((sum, b) => sum + parseFloat(b.harga || 0), 0);
            document.getElementById('totalRevenue').textContent = 'Rp ' + revenue.toLocaleString('id-ID');
        }

        const recentList = document.getElementById('recentBookings');
        recentList.innerHTML = '';
        
        bookings.slice(0, 5).forEach(booking => {
            const li = document.createElement('li');
            li.innerHTML = `
                <div>
                    <strong>${booking.nama_pelanggan}</strong>
                    <p>${booking.nama_layanan} - ${booking.tanggal}</p>
                </div>
                <span class="status-${booking.status_booking}">${booking.status_booking}</span>
            `;
            recentList.appendChild(li);
        });

    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}