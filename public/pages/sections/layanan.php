<?php
// FILE: layanan.php
// Includes modal component:
//   - modalTambahLayanan.php  → untuk tombol + Tambah Layanan
//   - modalTambahBarber.php   → untuk tombol + Tambah Barber
// NOTE: modalBookingManual.php di-include di booking.php (bukan di sini)
?>

<!-- ============================================================
     HEADER — JANGAN DIUBAH
============================================================ -->
<header class="header">
    <div class="header-left">
        <h1>Layanan & Info Barber</h1>
        <p id="currentTime">-</p>
    </div>
    <div class="header-right">
        <span class="status-online">● Online</span>
        <span style="margin-right:15px;">
            <?php echo htmlspecialchars($nama); ?> (<?php echo $role; ?>)
        </span>
    </div>
</header>

<!-- ============================================================
     PANEL WRAPPER
============================================================ -->
<div class="panel">

    <div class="panel-header">
        <div>
            <h3 class="panel-title">Daftar Layanan</h3>
            <p class="panel-subtitle">Kelola layanan dan nominal harga layanan</p>
        </div>
        <!-- Tombol ini membuka modalTambahLayanan -->
        <button class="action-btn action-btn--primary" onclick="openTambahLayanan()">
            + Tambah Layanan
        </button>
    </div>

    <!-- SERVICE CARD GRID -->
    <div class="layanan-grid" id="layananGrid">
        <!-- Cards dirender via JS -->
    </div>

</div>

<div class="panel">

    <div class="panel-header">
        <div>
            <h3 class="panel-title">Info Barber</h3>
            <p class="panel-subtitle">Kelola informasi barber dan jadwal kerja</p>
        </div>
        <!-- Tombol ini membuka modalTambahBarber -->
        <button class="action-btn action-btn--primary" onclick="openTambahBarber()">
            + Tambah Barber
        </button>
    </div>

    <!-- BARBER CARD GRID -->
    <!-- FIX: id dipisah — barberGrid khusus untuk barber -->
    <div class="layanan-grid" id="barberGrid">
        <!-- Cards dirender via JS -->
    </div>

</div>


<!-- ============================================================
     MODAL COMPONENTS
     Di-include di bawah konten utama, sebelum </body>
============================================================ -->
<?php include __DIR__ . '/../../components/ui/modalTambahLayanan.php'; ?>
<?php include __DIR__ . '/../../components/ui/modalTambahBarber.php'; ?>


<script>
(function () {

    /* ============================================================
       CONFIG
    ============================================================ */
    var DUMMY_MODE = true;

    /* ============================================================
       DUMMY DATA — LAYANAN
       Kontrak field FE ↔ BE:
         id          : number  — ID unik layanan
         nama        : string  — Nama layanan
         durasi      : string  — Durasi (misal "60 Menit")
         harga       : number  — Harga layanan
         deskripsi   : string  — Deskripsi singkat
         ikon        : string  — Emoji / kode ikon (opsional)
    ============================================================ */
    function getDummyLayanan() {
        return [
            { id: 1, nama: 'Haircut Booking',    durasi: '60 Menit', harga: 50000,  deskripsi: 'Potongan sesuai request & hairwash',  ikon: '✂️' },
            { id: 2, nama: 'Cukur + Beard',      durasi: '45 Menit', harga: 55000,  deskripsi: 'Cukur rambut dan shaping jenggot',     ikon: '✂️' },
            { id: 3, nama: 'Haircut + Coloring', durasi: '90 Menit', harga: 120000, deskripsi: 'Potong rambut dan pewarnaan rambut',   ikon: '✂️' },
        ];
    }

    /* ============================================================
       DUMMY DATA — BARBER
       Kontrak field FE ↔ BE:
         id          : number  — ID unik barber
         nama        : string  — Nama barber
         jadwal      : string  — Jam kerja (misal "09:00 - 17:00")
         hari        : string  — Hari kerja (misal "Senin - Jumat")
         deskripsi   : string  — Deskripsi singkat barber
         ikon        : string  — Emoji / kode ikon (opsional)
    ============================================================ */
    function getDummyBarber() {
        return [
            { id: 1, nama: 'Abenk', jadwal: '09:00 - 17:00', hari: 'Senin - Jumat', deskripsi: 'Barber senior dengan pengalaman 10 tahun', ikon: '👤' },
        ];
    }

    /* ============================================================
       HELPER: format rupiah
    ============================================================ */
    function rupiah(n) {
        if (typeof formatRupiah === 'function') return formatRupiah(n);
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    /* ============================================================
       HELPER: safe get element by id (guard terhadap DOM null)
    ============================================================ */
    function getEl(id) {
        var el = document.getElementById(id);
        if (!el) console.warn('[layanan.php] Element #' + id + ' tidak ditemukan.');
        return el;
    }

    /* ============================================================
       INIT — render layanan & barber secara paralel
    ============================================================ */
    (function init() {
        var layananData = DUMMY_MODE ? getDummyLayanan() : [];
        var barberData  = DUMMY_MODE ? getDummyBarber()  : [];

        renderLayananGrid(layananData);
        renderBarberGrid(barberData);
    })();


    /* ============================================================
       ── LAYANAN ────────────────────────────────────────────────
    ============================================================ */

    /* Render semua card layanan ke #layananGrid */
    function renderLayananGrid(layananList) {
        var grid = getEl('layananGrid');
        if (!grid) return;

        var html = layananList.map(function (l) {
            return buildLayananCard(l);
        }).join('');

        /* Tambah placeholder card di akhir */
        html += buildAddLayananCard();

        grid.innerHTML = html;
    }

    /* Build satu card layanan */
    function buildLayananCard(l) {
        return '<div class="layanan-card">'
            + '<div class="layanan-card__icon">'   + (l.ikon      || '✂️') + '</div>'
            + '<div class="layanan-card__nama">'   + (l.nama      || '-')  + '</div>'
            + '<div class="layanan-card__durasi">' + (l.durasi    || '-')  + '</div>'
            + '<div class="layanan-card__harga">'  + rupiah(l.harga || 0)  + '</div>'
            + '<div class="layanan-card__desc">'   + (l.deskripsi || '')   + '</div>'
            + '<div class="layanan-card__divider"></div>'
            + '<div class="layanan-card__actions">'
            +   '<button class="layanan-btn layanan-btn--edit"  onclick="editLayanan('  + l.id + ')">Edit</button>'
            +   '<button class="layanan-btn layanan-btn--hapus" onclick="hapusLayanan(' + l.id + ')">Hapus</button>'
            + '</div>'
            + '</div>';
    }

    /* Build placeholder tambah layanan */
    function buildAddLayananCard() {
        return '<div class="layanan-card layanan-card--add" onclick="openTambahLayanan()">'
            + '<div class="layanan-card__add-inner">'
            +   '<span class="layanan-card__add-icon">+</span>'
            +   '<span class="layanan-card__add-label">Tambah Layanan</span>'
            + '</div>'
            + '</div>';
    }


    /* ============================================================
       ── BARBER ─────────────────────────────────────────────────
    ============================================================ */

    /* Render semua card barber ke #barberGrid */
    function renderBarberGrid(barberList) {
        var grid = getEl('barberGrid');
        if (!grid) return;

        var html = barberList.map(function (b) {
            return buildBarberCard(b);
        }).join('');

        /* Tambah placeholder card di akhir */
        html += buildAddBarberCard();

        grid.innerHTML = html;
    }

    /* Build satu card barber */
    function buildBarberCard(b) {
        return '<div class="layanan-card">'
            + '<div class="layanan-card__icon">'   + (b.ikon      || '👤') + '</div>'
            + '<div class="layanan-card__nama">'   + (b.nama      || '-')  + '</div>'
            + '<div class="layanan-card__durasi">' + (b.hari      || '-')  + '</div>'
            + '<div class="layanan-card__harga">'  + (b.jadwal    || '-')  + '</div>'
            + '<div class="layanan-card__desc">'   + (b.deskripsi || '')   + '</div>'
            + '<div class="layanan-card__divider"></div>'
            + '<div class="layanan-card__actions">'
            +   '<button class="layanan-btn layanan-btn--edit"  onclick="editBarber('  + b.id + ')">Edit</button>'
            +   '<button class="layanan-btn layanan-btn--hapus" onclick="hapusBarber(' + b.id + ')">Hapus</button>'
            + '</div>'
            + '</div>';
    }

    /* Build placeholder tambah barber */
    function buildAddBarberCard() {
        return '<div class="layanan-card layanan-card--add" onclick="openTambahBarber()">'
            + '<div class="layanan-card__add-inner">'
            +   '<span class="layanan-card__add-icon">+</span>'
            +   '<span class="layanan-card__add-label">Tambah Barber</span>'
            + '</div>'
            + '</div>';
    }


    /* ============================================================
       ACTION — LAYANAN
       Edit: buka modal dalam mode Edit dengan data yang sudah ada.
       Hapus: confirm → stub ke BE.
       openTambahLayanan() didefinisikan di modalTambahLayanan.php
    ============================================================ */
    window.editLayanan = function (id) {
        /* TODO: ambil data dari BE. Sementara pakai dummy. */
        var data = getDummyLayanan().find(function (l) { return l.id === id; });
        if (!data) { console.warn('editLayanan: data id', id, 'tidak ditemukan'); return; }

        /* Buka modal dalam mode Edit, oper data yang sudah ada */
        openTambahLayanan(data);
    };

    window.hapusLayanan = function (id) {
        if (!confirm('Hapus layanan ID ' + id + '?')) return;
        /* TODO: kirim request DELETE ke BE */
        console.log('[layanan] hapus id:', id);
        alert('TODO: hapus layanan ke BE');
    };


    /* ============================================================
       ACTION — BARBER
       Edit: buka modal dalam mode Edit dengan data yang sudah ada.
       Hapus: confirm → stub ke BE.
       openTambahBarber() didefinisikan di modalTambahBarber.php
    ============================================================ */
    window.editBarber = function (id) {
        /* TODO: ambil data dari BE. Sementara pakai dummy. */
        var data = getDummyBarber().find(function (b) { return b.id === id; });
        if (!data) { console.warn('editBarber: data id', id, 'tidak ditemukan'); return; }

        /* Buka modal dalam mode Edit, oper data yang sudah ada */
        openTambahBarber(data);
    };

    window.hapusBarber = function (id) {
        if (!confirm('Hapus barber ID ' + id + '?')) return;
        /* TODO: kirim request DELETE ke BE */
        console.log('[barber] hapus id:', id);
        alert('TODO: hapus barber ke BE');
    };

})();
</script>