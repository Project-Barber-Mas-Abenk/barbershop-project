<?php
// FILE: booking.php
// Modal Booking Manual di-include di sini karena section ini
// yang punya tombol "+ Tambah Booking".
?>

<!-- ============================================================
     HEADER — JANGAN DIUBAH
============================================================ -->
<header class="header">

    <div class="header-left">
        <h1>Daftar Booking</h1>
        <p id="currentTime">-</p>
    </div>

    <div class="header-right">
        <span class="status-online">● Online</span>

        <span style="margin-right:15px;">
            <?php echo htmlspecialchars($nama); ?> (<?php echo $role; ?>)
        </span>
    </div>

</header>

<!-- ACTION BAR -->
<div class="action-bar">
    <button class="action-btn" onclick="filterBookings('semua')">Semua Booking</button>
    <button class="action-btn" onclick="filterBookings('menunggu')">Menunggu</button>
    <button class="action-btn" onclick="filterBookings('proses')">Proses</button>
    <button class="action-btn" onclick="filterBookings('selesai')">Selesai</button>
    <button class="action-btn" onclick="filterBookings('dibatalkan')">Dibatalkan</button>
    <!-- Tombol ini membuka modalBookingManual -->
    <button class="action-btn action-btn--primary" onclick="openModalBookingManual()">+ Tambah Booking</button>
</div>

<!-- TOOLBAR: search + filter -->
<div class="booking-toolbar">
    <div class="booking-search">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" class="search-input"
               placeholder="Cari nama pelanggan, layanan, barber..."
               oninput="applyFilters()">
    </div>
    <div class="booking-filter-group">
        <select id="filterTanggal" class="filter-select" onchange="applyFilters()">
            <option value="semua">Semua Tanggal</option>
            <option value="hari_ini">Hari Ini</option>
            <option value="besok">Besok</option>
            <option value="minggu_ini">Minggu Ini</option>
        </select>
        <select id="filterBarber" class="filter-select" onchange="applyFilters()">
            <option value="semua">Semua Barber</option>
        </select>
        <select id="filterStatus" class="filter-select" onchange="applyFilters()">
            <option value="semua">Semua Status</option>
            <option value="menunggu">Menunggu</option>
            <option value="proses">Proses</option>
            <option value="dikonfirmasi">Dikonfirmasi</option>
            <option value="selesai">Selesai</option>
            <option value="dibatalkan">Dibatalkan</option>
        </select>
    </div>
</div>

<!-- SUMMARY CHIPS -->
<div class="booking-summary-row">
    <div class="bsum-card">
        <span class="bsum-num"                   id="bsumTotal">0</span>
        <span class="bsum-label">Total</span>
    </div>
    <div class="bsum-card">
        <span class="bsum-num bsum-num--pending"  id="bsumMenunggu">0</span>
        <span class="bsum-label">Menunggu</span>
    </div>
    <div class="bsum-card">
        <span class="bsum-num bsum-num--proses"   id="bsumProses">0</span>
        <span class="bsum-label">Proses</span>
    </div>
    <div class="bsum-card">
        <span class="bsum-num bsum-num--selesai"  id="bsumSelesai">0</span>
        <span class="bsum-label">Selesai</span>
    </div>
    <div class="bsum-card">
        <span class="bsum-num bsum-num--batal"    id="bsumBatal">0</span>
        <span class="bsum-label">Dibatalkan</span>
    </div>
</div>

<!-- TABLE PANEL -->
<div class="panel">
    <div class="panel-header">
        <div>
            <h3 class="panel-title">Daftar Booking</h3>
            <p class="panel-subtitle" id="bookingTableSubtitle">Memuat data...</p>
        </div>
        <div class="panel-header-right">
            <div class="antrian-badge" id="bookingCount">0 Data</div>
        </div>
    </div>

    <!-- Header kolom -->
    <div class="btable-header">
        <div class="btable-col btable-col--no">No</div>
        <div class="btable-col btable-col--pelanggan">Pelanggan</div>
        <div class="btable-col btable-col--layanan">Layanan</div>
        <div class="btable-col btable-col--barber">Barber</div>
        <div class="btable-col btable-col--waktu">Waktu</div>
        <div class="btable-col btable-col--bayar">Bayar</div>
        <div class="btable-col btable-col--status">Status</div>
        <div class="btable-col btable-col--aksi">Aksi</div>
    </div>

    <!-- Isi baris -->
    <div id="bookingTableBody">
        <div class="empty-state">Memuat data...</div>
    </div>

    <!-- Pagination -->
    <div class="btable-pagination">
        <button class="page-btn" id="btnPrev" onclick="changePage(-1)">&#8592; Prev</button>
        <span class="page-info" id="pageInfo">Halaman 1 / 1</span>
        <button class="page-btn" id="btnNext" onclick="changePage(1)">Next &#8594;</button>
    </div>
</div>


<!-- ============================================================
     MODAL COMPONENT — Booking Manual
     Di-include di sini (bukan di layanan.php) karena tombol
     "+ Tambah Booking" ada di section ini.
============================================================ -->
<?php include __DIR__ . '/../../components/ui/modalBookingManual.php'; ?>


<script>
(function () {   /* IIFE — agar variabel tidak bocor ke global */

    /* ============================================================
       CONFIG
    ============================================================ */
    var PAGE_SIZE    = 10;
    var currentPage  = 1;
    var allBookings  = [];
    var filteredData = [];

    var DUMMY_MODE   = true;   // Set false saat BE siap
    var userRole     = '<?php echo $role; ?>';

    /* ============================================================
       FALLBACK formatRupiah
    ============================================================ */
    function _rupiah(n) {
        if (typeof formatRupiah === 'function') return formatRupiah(n);
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    /* ============================================================
       DUMMY DATA
       Kontrak field FE ↔ BE:
         id, nama_pelanggan, no_hp, nama_layanan, nama_barber,
         tanggal (YYYY-MM-DD), jam (HH:MM-HH:MM),
         status_booking: menunggu|proses|selesai|dikonfirmasi|dibatalkan
         status_bayar:   lunas|belum_lunas|pending
         harga: number
    ============================================================ */
    function getDummyBookings() {
        var today     = new Date().toISOString().slice(0, 10);
        var tomorrow  = new Date(Date.now() + 86400000).toISOString().slice(0, 10);
        var yesterday = new Date(Date.now() - 86400000).toISOString().slice(0, 10);
        return [
            { id:1,  nama_pelanggan:'Reza Firmansyah', no_hp:'08111111111', nama_layanan:'Haircut + Cuci',     nama_barber:'Abeng', tanggal:today,     jam:'08:00-09:00', status_booking:'selesai',      status_bayar:'lunas',       harga:45000  },
            { id:2,  nama_pelanggan:'Dimas Pratama',   no_hp:'08122222222', nama_layanan:'Haircut Booking',    nama_barber:'Abeng', tanggal:today,     jam:'08:00-09:00', status_booking:'selesai',      status_bayar:'lunas',       harga:35000  },
            { id:3,  nama_pelanggan:'Yoga Aditya',     no_hp:'08133333333', nama_layanan:'Cukur + Beard',      nama_barber:'Abeng', tanggal:today,     jam:'09:00-10:00', status_booking:'proses',       status_bayar:'belum_lunas', harga:55000  },
            { id:4,  nama_pelanggan:'Fajar Nugroho',   no_hp:'08144444444', nama_layanan:'Haircut Booking',    nama_barber:'Rizal', tanggal:today,     jam:'09:00-10:00', status_booking:'menunggu',     status_bayar:'belum_lunas', harga:35000  },
            { id:5,  nama_pelanggan:'Budi Santoso',    no_hp:'08155555555', nama_layanan:'Haircut + Coloring', nama_barber:'Rizal', tanggal:today,     jam:'10:00-11:00', status_booking:'menunggu',     status_bayar:'belum_lunas', harga:120000 },
            { id:6,  nama_pelanggan:'Gilang Saputra',  no_hp:'08166666666', nama_layanan:'Haircut + Cuci',     nama_barber:'Rizal', tanggal:today,     jam:'11:00-12:00', status_booking:'selesai',      status_bayar:'lunas',       harga:45000  },
            { id:7,  nama_pelanggan:'Arif Hidayat',    no_hp:'08177777777', nama_layanan:'Haircut Booking',    nama_barber:'Abeng', tanggal:today,     jam:'13:00-14:00', status_booking:'dibatalkan',   status_bayar:'belum_lunas', harga:35000  },
            { id:8,  nama_pelanggan:'Andi Wijaya',     no_hp:'08188888888', nama_layanan:'Haircut Booking',    nama_barber:'Abeng', tanggal:tomorrow,  jam:'08:00-09:00', status_booking:'dikonfirmasi', status_bayar:'belum_lunas', harga:35000  },
            { id:9,  nama_pelanggan:'Hendra Kusuma',   no_hp:'08199999999', nama_layanan:'Cukur + Beard',      nama_barber:'Abeng', tanggal:tomorrow,  jam:'09:00-10:00', status_booking:'dikonfirmasi', status_bayar:'belum_lunas', harga:55000  },
            { id:10, nama_pelanggan:'Surya Mahendra',  no_hp:'08100000000', nama_layanan:'Haircut Booking',    nama_barber:'Rizal', tanggal:tomorrow,  jam:'10:00-11:00', status_booking:'menunggu',     status_bayar:'belum_lunas', harga:35000  },
            { id:11, nama_pelanggan:'Kevin Pratama',   no_hp:'08121212121', nama_layanan:'Haircut + Cuci',     nama_barber:'Rizal', tanggal:yesterday, jam:'08:00-09:00', status_booking:'selesai',      status_bayar:'lunas',       harga:45000  },
            { id:12, nama_pelanggan:'Wahyu Setiawan',  no_hp:'08134343434', nama_layanan:'Cukur + Beard',      nama_barber:'Abeng', tanggal:yesterday, jam:'10:00-11:00', status_booking:'selesai',      status_bayar:'lunas',       harga:55000  },
            { id:13, nama_pelanggan:'Teguh Wibowo',    no_hp:'08145656565', nama_layanan:'Haircut Booking',    nama_barber:'Rizal', tanggal:yesterday, jam:'14:00-15:00', status_booking:'selesai',      status_bayar:'lunas',       harga:35000  },
        ];
    }

    /* ============================================================
       INIT — jalan langsung, tidak perlu DOMContentLoaded
       karena script ini ada SETELAH semua elemen di atas
    ============================================================ */
    (function init() {
        allBookings  = DUMMY_MODE ? getDummyBookings() : [];
        filteredData = allBookings.slice();
        populateBarberFilter();
        updateSummary();
        renderTable();
    })();

    /* ============================================================
       POPULATE BARBER FILTER
    ============================================================ */
    function populateBarberFilter() {
        var sel = document.getElementById('filterBarber');
        if (!sel) return;
        var barbers = [];
        allBookings.forEach(function(b) {
            if (b.nama_barber && barbers.indexOf(b.nama_barber) === -1) {
                barbers.push(b.nama_barber);
            }
        });
        barbers.forEach(function(name) {
            var opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            sel.appendChild(opt);
        });
    }

    /* ============================================================
       FILTER
    ============================================================ */
    window.filterBookings = function(status) {
        var sel = document.getElementById('filterStatus');
        if (sel) sel.value = status;
        applyFilters();
    };

    window.applyFilters = function() {
        var keyword    = ((document.getElementById('searchInput')  || {}).value || '').toLowerCase().trim();
        var tanggalVal = (document.getElementById('filterTanggal') || {}).value || 'semua';
        var barberVal  = (document.getElementById('filterBarber')  || {}).value || 'semua';
        var statusVal  = (document.getElementById('filterStatus')  || {}).value || 'semua';

        var today    = new Date().toISOString().slice(0, 10);
        var tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);
        var d = new Date(), day = d.getDay();
        var diff     = d.getDate() - day + (day === 0 ? -6 : 1);
        var weekStart = new Date(new Date().setDate(diff)).toISOString().slice(0, 10);
        var weekEnd   = new Date(new Date(weekStart).getTime() + 6 * 86400000).toISOString().slice(0, 10);

        filteredData = allBookings.filter(function(b) {
            if (keyword) {
                var hay = ((b.nama_pelanggan || '') + ' ' + (b.nama_layanan || '') + ' ' + (b.nama_barber || '')).toLowerCase();
                if (hay.indexOf(keyword) === -1) return false;
            }
            if (tanggalVal === 'hari_ini'   && b.tanggal !== today)    return false;
            if (tanggalVal === 'besok'      && b.tanggal !== tomorrow)  return false;
            if (tanggalVal === 'minggu_ini' && (b.tanggal < weekStart || b.tanggal > weekEnd)) return false;
            if (barberVal  !== 'semua'      && b.nama_barber    !== barberVal)  return false;
            if (statusVal  !== 'semua'      && b.status_booking !== statusVal)  return false;
            return true;
        });

        currentPage = 1;
        updateSummary();
        renderTable();
    };

    /* ============================================================
       SUMMARY
    ============================================================ */
    function updateSummary() {
        function set(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; }
        set('bsumTotal',    filteredData.length);
        set('bsumMenunggu', filteredData.filter(function(b){ return b.status_booking === 'menunggu'; }).length);
        set('bsumProses',   filteredData.filter(function(b){ return b.status_booking === 'proses'; }).length);
        set('bsumSelesai',  filteredData.filter(function(b){ return b.status_booking === 'selesai' || b.status_booking === 'dikonfirmasi'; }).length);
        set('bsumBatal',    filteredData.filter(function(b){ return b.status_booking === 'dibatalkan'; }).length);
    }

    /* ============================================================
       RENDER TABLE
    ============================================================ */
    function renderTable() {
        var body    = document.getElementById('bookingTableBody');
        var total   = filteredData.length;
        var maxPage = Math.max(1, Math.ceil(total / PAGE_SIZE));
        if (currentPage > maxPage) currentPage = maxPage;

        var start = (currentPage - 1) * PAGE_SIZE;
        var slice = filteredData.slice(start, start + PAGE_SIZE);

        function set(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; }
        function setDisabled(id, v) { var el = document.getElementById(id); if (el) el.disabled = v; }

        set('bookingCount',         total + ' Data');
        set('bookingTableSubtitle', total + ' booking ditemukan');
        set('pageInfo',             'Halaman ' + currentPage + ' / ' + maxPage);
        setDisabled('btnPrev', currentPage <= 1);
        setDisabled('btnNext', currentPage >= maxPage);

        if (!body) return;

        if (slice.length === 0) {
            body.innerHTML = '<div class="empty-state">Tidak ada booking yang sesuai filter</div>';
            return;
        }

        body.innerHTML = slice.map(function(b, idx) {
            return buildRow(b, start + idx + 1);
        }).join('');
    }

    /* ============================================================
       BUILD ROW
    ============================================================ */
    function buildRow(b, no) {
        var sCls  = statusClass(b.status_booking);
        var sIcon = statusIcon(b.status_booking);
        var bCls  = b.status_bayar === 'lunas' ? 'bayar--lunas' : 'bayar--belum';
        var bTxt  = b.status_bayar === 'lunas' ? '&#10003; Lunas' : '&#9679; Belum';
        var init  = (b.nama_pelanggan || '?').substring(0, 2).toUpperCase();
        var tgl   = fmtTanggal(b.tanggal);

        return '<div class="btable-row">'
            + '<div class="btable-col btable-col--no">' + no + '</div>'

            + '<div class="btable-col btable-col--pelanggan">'
            +   '<div class="row-avatar">' + init + '</div>'
            +   '<div class="row-pelanggan-info">'
            +     '<div class="row-name">' + (b.nama_pelanggan || '-') + '</div>'
            +     '<div class="row-hp">'   + (b.no_hp || '-') + '</div>'
            +   '</div>'
            + '</div>'

            + '<div class="btable-col btable-col--layanan">'
            +   '<div class="row-layanan">' + (b.nama_layanan || '-') + '</div>'
            +   '<div class="row-harga">'   + _rupiah(b.harga || 0) + '</div>'
            + '</div>'

            + '<div class="btable-col btable-col--barber">'
            +   '<span class="row-barber-badge">' + (b.nama_barber || '-') + '</span>'
            + '</div>'

            + '<div class="btable-col btable-col--waktu">'
            +   '<div class="row-tanggal">' + tgl + '</div>'
            +   '<div class="row-jam">'     + (b.jam || '-') + '</div>'
            + '</div>'

            + '<div class="btable-col btable-col--bayar">'
            +   '<span class="bayar-badge ' + bCls + '">' + bTxt + '</span>'
            + '</div>'

            + '<div class="btable-col btable-col--status">'
            +   '<span class="booking-status ' + sCls + '">' + sIcon + ' ' + (b.status_booking || '-') + '</span>'
            + '</div>'

            + '<div class="btable-col btable-col--aksi">'
            +   '<div class="row-aksi-group">'
            +     '<button class="aksi-btn aksi-btn--detail"  onclick="lihatDetail('  + b.id + ')" title="Detail">&#128065;</button>'
            +     '<button class="aksi-btn aksi-btn--konfirm" onclick="konfirmBooking(' + b.id + ')" title="Konfirmasi">&#10003;</button>'
            +     '<button class="aksi-btn aksi-btn--batal"   onclick="batalBooking('  + b.id + ')" title="Batalkan">&#10005;</button>'
            +   '</div>'
            + '</div>'

            + '</div>';
    }

    /* ============================================================
       HELPERS
    ============================================================ */
    function statusClass(s) {
        if (s === 'selesai' || s === 'dikonfirmasi') return 'status--selesai';
        if (s === 'proses')     return 'status--proses';
        if (s === 'dibatalkan') return 'status--batal';
        return 'status--pending';
    }

    function statusIcon(s) {
        if (s === 'selesai' || s === 'dikonfirmasi') return '&#10003;';
        if (s === 'proses')     return '&#9679;';
        if (s === 'dibatalkan') return '&#10005;';
        return '&#9203;';
    }

    function fmtTanggal(t) {
        if (!t) return '-';
        var p = t.split('-');
        var m = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return p[2] + ' ' + m[parseInt(p[1]) - 1] + ' ' + p[0];
    }

    /* ============================================================
       PAGINATION
    ============================================================ */
    window.changePage = function(dir) {
        var max = Math.max(1, Math.ceil(filteredData.length / PAGE_SIZE));
        currentPage = Math.min(Math.max(1, currentPage + dir), max);
        renderTable();
    };

    /* ============================================================
       ACTION STUBS — sambungkan ke BE
       openModalBookingManual() → sudah didefinisikan di modalBookingManual.php
    ============================================================ */
    window.lihatDetail    = function(id) { alert('TODO: detail booking ID ' + id); };
    window.konfirmBooking = function(id) { if (confirm('Konfirmasi booking ID ' + id + '?')) alert('TODO: konfirmasi ke BE'); };
    window.batalBooking   = function(id) { if (confirm('Batalkan booking ID '  + id + '?')) alert('TODO: batal ke BE'); };

})();
</script>