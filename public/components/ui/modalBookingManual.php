<?php
// FILE: modalBookingManual.php
// USAGE : <?php include 'modalBookingManual.php';
// OPEN  : openModalBookingManual()
// CLOSE : closeModalBookingManual()
?>

<!-- ============================================================
     STYLE — self-contained. Hapus jika class sudah ada di
     dashboard.css agar tidak duplikat.
============================================================ -->
<style>
/* Overlay: HIDDEN by default */
#overlayBookingManual {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.60);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
#overlayBookingManual.modal-overlay--active { display: flex; }

#overlayBookingManual .bkm-modal {
    background: #fff;
    border-radius: 10px;
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 8px 32px rgba(0,0,0,.22);
}
#overlayBookingManual .bkm-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px 14px;
    border-bottom: 1px solid #e5e7eb;
}
#overlayBookingManual .bkm-modal__title {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}
#overlayBookingManual .bkm-modal__close {
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
#overlayBookingManual .bkm-modal__close:hover { opacity: .6; }
#overlayBookingManual .bkm-modal__body {
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
#overlayBookingManual .bkm-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 24px 18px;
    border-top: 1px solid #e5e7eb;
}
#overlayBookingManual .f-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
#overlayBookingManual .f-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
#overlayBookingManual .f-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #6b7280;
}
#overlayBookingManual .f-label--opt {
    font-weight: 400;
    text-transform: none;
    letter-spacing: 0;
    font-size: 10px;
    color: #9ca3af;
}
#overlayBookingManual .f-input,
#overlayBookingManual .f-select {
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
#overlayBookingManual .f-input:focus,
#overlayBookingManual .f-select:focus { border-color: #b8860b; }
#overlayBookingManual .f-input::placeholder { color: #9ca3af; }
#overlayBookingManual .btn-sec {
    padding: 8px 20px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: transparent;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
}
#overlayBookingManual .btn-sec:hover { background: #f3f4f6; }
@media (max-width: 480px) {
    #overlayBookingManual .f-row { grid-template-columns: 1fr; }
    #overlayBookingManual .bkm-modal { max-width: 95vw; }
}
</style>

<!-- ============================================================
     MODAL — BOOKING MANUAL
============================================================ -->
<div id="overlayBookingManual" onclick="closeModalBookingManual()">

    <div class="bkm-modal" onclick="event.stopPropagation()">

        <!-- HEADER -->
        <div class="bkm-modal__header">
            <h3 class="bkm-modal__title">Booking Manual</h3>
            <button class="bkm-modal__close" onclick="closeModalBookingManual()">&#x2715;</button>
        </div>

        <!-- BODY -->
        <div class="bkm-modal__body">

            <!-- ROW 1: Nama Pelanggan | No HP -->
            <div class="f-row">
                <div class="f-group">
                    <label class="f-label" for="bm_nama">Nama Pelanggan</label>
                    <input class="f-input" type="text" id="bm_nama"
                           placeholder="Nama Lengkap..." autocomplete="off">
                </div>
                <div class="f-group">
                    <label class="f-label" for="bm_nohp">No HP</label>
                    <input class="f-input" type="tel" id="bm_nohp"
                           placeholder="08xxxxxxxxxx..." autocomplete="off">
                </div>
            </div>

            <!-- ROW 2: Layanan | Jam -->
            <div class="f-row">
                <div class="f-group">
                    <label class="f-label" for="bm_layanan">Layanan</label>
                    <select class="f-select" id="bm_layanan">
                        <option value="" disabled selected>Pilih Layanan</option>
                        <option value="haircut">Haircut Booking</option>
                        <option value="beard">Beard Trim</option>
                        <option value="coloring">Coloring</option>
                        <!-- TODO: opsi diisi dinamis via JS / BE -->
                    </select>
                </div>
                <div class="f-group">
                    <label class="f-label" for="bm_jam">Jam</label>
                    <input class="f-input" type="time" id="bm_jam" value="09:00">
                </div>
            </div>

            <!-- ROW 3: Tanggal | Catatan (opsional) -->
            <div class="f-row">
                <div class="f-group">
                    <label class="f-label" for="bm_tanggal">Tanggal</label>
                    <input class="f-input" type="date" id="bm_tanggal">
                </div>
                <div class="f-group">
                    <label class="f-label" for="bm_catatan">
                        Catatan <span class="f-label--opt">(Opsional)</span>
                    </label>
                    <input class="f-input" type="text" id="bm_catatan"
                           placeholder="Comma Hair, Mohawk, dll..." autocomplete="off">
                </div>
            </div>

        </div><!-- /bkm-modal__body -->

        <!-- FOOTER -->
        <div class="bkm-modal__footer">
            <button class="btn-sec" onclick="closeModalBookingManual()">Batal</button>
            <button class="action-btn action-btn--primary" onclick="submitBookingManual()">Simpan</button>
        </div>

    </div><!-- /bkm-modal -->

</div><!-- /#overlayBookingManual -->


<script>
(function () {

    /* ── OPEN ─────────────────────────────────────────────────── */
    window.openModalBookingManual = function () {
        var overlay = document.getElementById('overlayBookingManual');
        if (!overlay) { console.warn('[modalBookingManual] overlay tidak ditemukan'); return; }

        /* Set tanggal default = hari ini jika belum terisi */
        var tanggalEl = document.getElementById('bm_tanggal');
        if (tanggalEl && !tanggalEl.value) {
            tanggalEl.value = _todayISO();
        }

        overlay.classList.add('modal-overlay--active');
        document.body.style.overflow = 'hidden';
    };

    /* ── CLOSE ────────────────────────────────────────────────── */
    window.closeModalBookingManual = function () {
        var overlay = document.getElementById('overlayBookingManual');
        if (!overlay) return;
        overlay.classList.remove('modal-overlay--active');
        document.body.style.overflow = '';
    };

    /* ── SUBMIT STUB ──────────────────────────────────────────── */
    window.submitBookingManual = function () {
        var payload = {
            nama    : _getVal('bm_nama'),
            nohp    : _getVal('bm_nohp'),
            layanan : _getVal('bm_layanan'),
            jam     : _getVal('bm_jam'),
            tanggal : _getVal('bm_tanggal'),
            catatan : _getVal('bm_catatan'),
        };

        if (!payload.nama || !payload.layanan || !payload.tanggal) {
            alert('Nama pelanggan, layanan, dan tanggal wajib diisi.');
            return;
        }

        /* TODO: ganti alert ini dengan fetch ke endpoint BE */
        console.log('[modalBookingManual] payload:', payload);
        alert('TODO: simpan booking manual ke BE');
    };

    /* ── PRIVATE HELPERS ──────────────────────────────────────── */
    function _getVal(id) {
        var el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    function _todayISO() {
        var d  = new Date();
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        var dd = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + mm + '-' + dd;
    }

})();
</script>