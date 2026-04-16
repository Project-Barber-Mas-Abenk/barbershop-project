<?php
// FILE: modalTambahLayanan.php
// USAGE : <?php include 'modalTambahLayanan.php';
// OPEN  : openTambahLayanan()          → mode Tambah
//         openTambahLayanan(dataObj)   → mode Edit (data di-prefill)
// CLOSE : closeTambahLayanan()
?>

<!-- ============================================================
     STYLE — self-contained agar modal jalan tanpa perlu edit
     dashboard.css. Jika class sudah ada di dashboard.css, blok
     ini bisa dihapus agar tidak duplikat.
============================================================ -->
<style>
/* Overlay: HIDDEN by default — ini kunci utama perbaikan bug */
#overlayTambahLayanan {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.60);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
#overlayTambahLayanan.modal-overlay--active { display: flex; }

#overlayTambahLayanan .lay-modal {
    background: #fff;
    border-radius: 10px;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 8px 32px rgba(0,0,0,.22);
}
#overlayTambahLayanan .lay-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px 14px;
    border-bottom: 1px solid #e5e7eb;
}
#overlayTambahLayanan .lay-modal__title {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}
#overlayTambahLayanan .lay-modal__close {
    background: none;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    width: 28px;
    height: 28px;
    cursor: pointer;
    font-size: 13px;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
}
#overlayTambahLayanan .lay-modal__close:hover { opacity: .6; }
#overlayTambahLayanan .lay-modal__body {
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
#overlayTambahLayanan .lay-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 24px 18px;
    border-top: 1px solid #e5e7eb;
}
/* 2-column row */
#overlayTambahLayanan .f-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
#overlayTambahLayanan .f-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
#overlayTambahLayanan .f-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #6b7280;
}
#overlayTambahLayanan .f-input {
    padding: 8px 11px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
    color: #111827;
    background: #fff;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
#overlayTambahLayanan .f-input:focus { border-color: #b8860b; }
#overlayTambahLayanan .f-input::placeholder { color: #9ca3af; }
/* Batal button — mengikuti gaya dashboard, varian sekunder */
#overlayTambahLayanan .btn-sec {
    padding: 8px 20px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: transparent;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
}
#overlayTambahLayanan .btn-sec:hover { background: #f3f4f6; }
@media (max-width: 480px) {
    #overlayTambahLayanan .f-row { grid-template-columns: 1fr; }
    #overlayTambahLayanan .lay-modal { max-width: 95vw; }
}
</style>

<!-- ============================================================
     MODAL — TAMBAH / EDIT LAYANAN
============================================================ -->
<div id="overlayTambahLayanan" onclick="closeTambahLayanan()">

    <div class="lay-modal" onclick="event.stopPropagation()">

        <!-- HEADER -->
        <div class="lay-modal__header">
            <h3 class="lay-modal__title" id="layananModalTitle">Tambah Layanan</h3>
            <button class="lay-modal__close" onclick="closeTambahLayanan()">&#x2715;</button>
        </div>

        <!-- BODY -->
        <div class="lay-modal__body">

            <!-- ID tersembunyi — diisi saat mode Edit -->
            <input type="hidden" id="lay_id">

            <!-- ROW 1: Nama Layanan | Durasi -->
            <div class="f-row">
                <div class="f-group">
                    <label class="f-label" for="lay_nama">Nama Layanan</label>
                    <input class="f-input" type="text" id="lay_nama"
                           placeholder="Haircut..." autocomplete="off">
                </div>
                <div class="f-group">
                    <label class="f-label" for="lay_durasi">Durasi (Menit)</label>
                    <input class="f-input" type="text" id="lay_durasi"
                           placeholder="60 Menit..." autocomplete="off">
                </div>
            </div>

            <!-- ROW 2: Harga | Deskripsi -->
            <div class="f-row">
                <div class="f-group">
                    <label class="f-label" for="lay_harga">Harga</label>
                    <input class="f-input" type="number" id="lay_harga"
                           placeholder="30000" min="0" step="1000">
                </div>
                <div class="f-group">
                    <label class="f-label" for="lay_deskripsi">Deskripsi Singkat</label>
                    <input class="f-input" type="text" id="lay_deskripsi"
                           placeholder="Termasuk Kramaas..." autocomplete="off">
                </div>
            </div>

        </div><!-- /lay-modal__body -->

        <!-- FOOTER -->
        <div class="lay-modal__footer">
            <button class="btn-sec" onclick="closeTambahLayanan()">Batal</button>
            <button class="action-btn action-btn--primary" onclick="submitTambahLayanan()">Simpan</button>
        </div>

    </div><!-- /lay-modal -->

</div><!-- /#overlayTambahLayanan -->


<script>
(function () {

    /* ── OPEN ─────────────────────────────────────────────────── */
    /* @param {object|null} data — null/undefined = Tambah, object = Edit */
    window.openTambahLayanan = function (data) {
        var overlay = document.getElementById('overlayTambahLayanan');
        if (!overlay) { console.warn('[modalTambahLayanan] overlay tidak ditemukan'); return; }

        var isEdit = !!(data && data.id);

        var titleEl = document.getElementById('layananModalTitle');
        if (titleEl) titleEl.textContent = isEdit ? 'Edit Layanan' : 'Tambah Layanan';

        _setVal('lay_id',        isEdit ? data.id        : '');
        _setVal('lay_nama',      isEdit ? data.nama       : '');
        _setVal('lay_durasi',    isEdit ? data.durasi     : '');
        _setVal('lay_harga',     isEdit ? data.harga      : '');
        _setVal('lay_deskripsi', isEdit ? data.deskripsi  : '');

        overlay.classList.add('modal-overlay--active');
        document.body.style.overflow = 'hidden';
    };

    /* ── CLOSE ────────────────────────────────────────────────── */
    window.closeTambahLayanan = function () {
        var overlay = document.getElementById('overlayTambahLayanan');
        if (!overlay) return;
        overlay.classList.remove('modal-overlay--active');
        document.body.style.overflow = '';
    };

    /* ── SUBMIT STUB ──────────────────────────────────────────── */
    window.submitTambahLayanan = function () {
        var payload = {
            id        : _getVal('lay_id'),
            nama      : _getVal('lay_nama'),
            durasi    : _getVal('lay_durasi'),
            harga     : _getVal('lay_harga'),
            deskripsi : _getVal('lay_deskripsi'),
        };

        if (!payload.nama || !payload.harga) {
            alert('Nama layanan dan harga wajib diisi.');
            return;
        }

        var isEdit = !!payload.id;
        /* TODO: ganti alert ini dengan fetch ke endpoint BE */
        console.log('[modalTambahLayanan] mode:', isEdit ? 'Edit' : 'Tambah', payload);
        alert('TODO: ' + (isEdit ? 'update' : 'simpan') + ' layanan ke BE');
    };

    /* ── PRIVATE HELPERS ──────────────────────────────────────── */
    function _getVal(id) {
        var el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }
    function _setVal(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = (val != null) ? val : '';
    }

})();
</script>