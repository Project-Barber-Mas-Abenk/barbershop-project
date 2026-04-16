<?php
// FILE: modalTambahBarber.php
// USAGE : <?php include 'modalTambahBarber.php';
// OPEN  : openTambahBarber()          → mode Tambah
//         openTambahBarber(dataObj)   → mode Edit (data di-prefill)
// CLOSE : closeTambahBarber()
?>

<!-- ============================================================
     STYLE — self-contained. Hapus jika class sudah ada di
     dashboard.css agar tidak duplikat.
============================================================ -->
<style>
/* Overlay: HIDDEN by default */
#overlayTambahBarber {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.60);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
#overlayTambahBarber.modal-overlay--active { display: flex; }

#overlayTambahBarber .brb-modal {
    background: #fff;
    border-radius: 10px;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 8px 32px rgba(0,0,0,.22);
}
#overlayTambahBarber .brb-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px 14px;
    border-bottom: 1px solid #e5e7eb;
}
#overlayTambahBarber .brb-modal__title {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}
#overlayTambahBarber .brb-modal__close {
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
#overlayTambahBarber .brb-modal__close:hover { opacity: .6; }
#overlayTambahBarber .brb-modal__body {
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
#overlayTambahBarber .brb-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 24px 18px;
    border-top: 1px solid #e5e7eb;
}
#overlayTambahBarber .f-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
#overlayTambahBarber .f-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
#overlayTambahBarber .f-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #6b7280;
}
#overlayTambahBarber .f-input,
#overlayTambahBarber .f-select {
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
#overlayTambahBarber .f-input:focus,
#overlayTambahBarber .f-select:focus  { border-color: #b8860b; }
#overlayTambahBarber .f-input::placeholder { color: #9ca3af; }
#overlayTambahBarber .btn-sec {
    padding: 8px 20px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: transparent;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
}
#overlayTambahBarber .btn-sec:hover { background: #f3f4f6; }
@media (max-width: 480px) {
    #overlayTambahBarber .f-row { grid-template-columns: 1fr; }
    #overlayTambahBarber .brb-modal { max-width: 95vw; }
}
</style>

<!-- ============================================================
     MODAL — TAMBAH / EDIT BARBER
============================================================ -->
<div id="overlayTambahBarber" onclick="closeTambahBarber()">

    <div class="brb-modal" onclick="event.stopPropagation()">

        <!-- HEADER -->
        <div class="brb-modal__header">
            <h3 class="brb-modal__title" id="barberModalTitle">Tambah Barber</h3>
            <button class="brb-modal__close" onclick="closeTambahBarber()">&#x2715;</button>
        </div>

        <!-- BODY -->
        <div class="brb-modal__body">

            <!-- ID tersembunyi — diisi saat mode Edit -->
            <input type="hidden" id="brb_id">

            <!-- ROW 1: Nama Barber | No HP -->
            <div class="f-row">
                <div class="f-group">
                    <label class="f-label" for="brb_nama">Nama Barber</label>
                    <input class="f-input" type="text" id="brb_nama"
                           placeholder="Nama Lengkap..." autocomplete="off">
                </div>
                <div class="f-group">
                    <label class="f-label" for="brb_nohp">No HP</label>
                    <input class="f-input" type="tel" id="brb_nohp"
                           placeholder="+62-81720..." autocomplete="off">
                </div>
            </div>

            <!-- ROW 2: Jabatan/Role | Status -->
            <div class="f-row">
                <div class="f-group">
                    <label class="f-label" for="brb_role">Jabatan / Role</label>
                    <select class="f-select" id="brb_role">
                        <option value="" disabled selected>Pilih Role</option>
                        <option value="senior">Senior Barber</option>
                        <option value="barber">Barber</option>
                        <option value="magang">Magang</option>
                    </select>
                </div>
                <div class="f-group">
                    <label class="f-label" for="brb_status">Status</label>
                    <select class="f-select" id="brb_status">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Non-Aktif</option>
                    </select>
                </div>
            </div>

        </div><!-- /brb-modal__body -->

        <!-- FOOTER -->
        <div class="brb-modal__footer">
            <button class="btn-sec" onclick="closeTambahBarber()">Batal</button>
            <button class="action-btn action-btn--primary" onclick="submitTambahBarber()">Simpan</button>
        </div>

    </div><!-- /brb-modal -->

</div><!-- /#overlayTambahBarber -->


<script>
(function () {

    /* ── OPEN ─────────────────────────────────────────────────── */
    /* @param {object|null} data — null/undefined = Tambah, object = Edit */
    window.openTambahBarber = function (data) {
        var overlay = document.getElementById('overlayTambahBarber');
        if (!overlay) { console.warn('[modalTambahBarber] overlay tidak ditemukan'); return; }

        var isEdit = !!(data && data.id);

        var titleEl = document.getElementById('barberModalTitle');
        if (titleEl) titleEl.textContent = isEdit ? 'Edit Barber' : 'Tambah Barber';

        _setVal('brb_id',     isEdit ? data.id     : '');
        _setVal('brb_nama',   isEdit ? data.nama   : '');
        _setVal('brb_nohp',   isEdit ? data.nohp   : '');
        _setVal('brb_role',   isEdit ? data.role   : '');
        _setVal('brb_status', isEdit ? data.status : 'aktif');

        overlay.classList.add('modal-overlay--active');
        document.body.style.overflow = 'hidden';
    };

    /* ── CLOSE ────────────────────────────────────────────────── */
    window.closeTambahBarber = function () {
        var overlay = document.getElementById('overlayTambahBarber');
        if (!overlay) return;
        overlay.classList.remove('modal-overlay--active');
        document.body.style.overflow = '';
    };

    /* ── SUBMIT STUB ──────────────────────────────────────────── */
    window.submitTambahBarber = function () {
        var payload = {
            id     : _getVal('brb_id'),
            nama   : _getVal('brb_nama'),
            nohp   : _getVal('brb_nohp'),
            role   : _getVal('brb_role'),
            status : _getVal('brb_status'),
        };

        if (!payload.nama || !payload.role) {
            alert('Nama barber dan role wajib diisi.');
            return;
        }

        var isEdit = !!payload.id;
        /* TODO: ganti alert ini dengan fetch ke endpoint BE */
        console.log('[modalTambahBarber] mode:', isEdit ? 'Edit' : 'Tambah', payload);
        alert('TODO: ' + (isEdit ? 'update' : 'simpan') + ' barber ke BE');
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