<?php
// FILE: layanan.php
?>

<!-- ============================================================
     HEADER — JANGAN DIUBAH
============================================================ -->
<header class="header">
    <div class="header-left">
        <h1>Layanan</h1>
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
        <button class="action-btn action-btn--primary" onclick="openTambahLayanan()">
            + Tambah Layanan
        </button>
    </div>

    <!-- SERVICE CARD GRID -->
    <div class="layanan-grid" id="layananGrid">
        <!-- Cards dirender via JS -->
    </div>

</div>


<script>
(function () {

    /* ============================================================
       CONFIG
    ============================================================ */
    var DUMMY_MODE = true;

    /* ============================================================
       DUMMY DATA
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
            { id:1, nama:'Haircut Booking',   durasi:'60 Menit', harga:50000,  deskripsi:'Potongan sesuai request & hairwash',    ikon:'✂️' },
            { id:2, nama:'Cukur + Beard',     durasi:'45 Menit', harga:55000,  deskripsi:'Cukur rambut dan shaping jenggot',       ikon:'✂️' },
            { id:3, nama:'Haircut + Coloring',durasi:'90 Menit', harga:120000, deskripsi:'Potong rambut dan pewarnaan rambut',     ikon:'✂️' },
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
       INIT
    ============================================================ */
    (function init() {
        var data = DUMMY_MODE ? getDummyLayanan() : [];
        renderGrid(data);
    })();

    /* ============================================================
       RENDER GRID
    ============================================================ */
    function renderGrid(layananList) {
        var grid = document.getElementById('layananGrid');
        if (!grid) return;

        var html = layananList.map(function(l) {
            return buildCard(l);
        }).join('');

        /* Tambah placeholder card di akhir */
        html += buildAddCard();

        grid.innerHTML = html;
    }

    /* ============================================================
       BUILD SERVICE CARD
    ============================================================ */
    function buildCard(l) {
        return '<div class="layanan-card">'
            + '<div class="layanan-card__icon">' + (l.ikon || '✂️') + '</div>'
            + '<div class="layanan-card__nama">'  + (l.nama || '-') + '</div>'
            + '<div class="layanan-card__durasi">' + (l.durasi || '-') + '</div>'
            + '<div class="layanan-card__harga">'  + rupiah(l.harga || 0) + '</div>'
            + '<div class="layanan-card__desc">'   + (l.deskripsi || '') + '</div>'
            + '<div class="layanan-card__divider"></div>'
            + '<div class="layanan-card__actions">'
            +   '<button class="layanan-btn layanan-btn--edit"  onclick="editLayanan('  + l.id + ')">Edit</button>'
            +   '<button class="layanan-btn layanan-btn--hapus" onclick="hapusLayanan(' + l.id + ')">Hapus</button>'
            + '</div>'
            + '</div>';
    }

    /* ============================================================
       BUILD ADD PLACEHOLDER CARD
    ============================================================ */
    function buildAddCard() {
        return '<div class="layanan-card layanan-card--add" onclick="openTambahLayanan()">'
            + '<div class="layanan-card__add-inner">'
            +   '<span class="layanan-card__add-icon">+</span>'
            +   '<span class="layanan-card__add-label">Tambah Layanan</span>'
            + '</div>'
            + '</div>';
    }

    /* ============================================================
       ACTION STUBS — sambungkan ke BE
    ============================================================ */
    window.openTambahLayanan = function() { alert('TODO: modal tambah layanan'); };
    window.editLayanan       = function(id) { alert('TODO: edit layanan ID ' + id); };
    window.hapusLayanan      = function(id) { if (confirm('Hapus layanan ID ' + id + '?')) alert('TODO: hapus ke BE'); };

})();
</script>