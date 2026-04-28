/**
 * booking.js — Booking page logic
 * public/client/assets/js/booking.js
 *
 * Responsibilities:
 *   1. Load barber list from API → GET /api/barbers.php
 *   2. Load service list from API → GET /api/services.php (optional, already static)
 *   3. Handle date change → load available time slots
 *   4. Submit booking → POST /api/booking.php
 */

/* ════════════════════════════════════════════
   HELPER
   ════════════════════════════════════════════ */
const getEl = (id) => document.getElementById(id);

const showStatus = (el, type, msg) => {
  if (!el) return;
  el.className = `form-message ${type}`;
  el.textContent = msg;
};


/* ════════════════════════════════════════════
   1. LOAD BARBERS DYNAMICALLY
   ════════════════════════════════════════════ */
(async function loadBarbers() {
  const barberSelect = getEl('book-barber');
  if (!barberSelect) return;

  try {
    const res = await fetch('/api/barbers.php');
    if (!res.ok) return; // silently fail — static fallback already in PHP

    const data = await res.json();
    const barbers = data.data || data;

    if (Array.isArray(barbers) && barbers.length) {
      // Clear existing options except the first placeholder
      while (barberSelect.options.length > 1) {
        barberSelect.remove(1);
      }

      barbers.forEach(b => {
        const opt = document.createElement('option');
        opt.value       = b.id;
        opt.textContent = b.name;
        barberSelect.appendChild(opt);
      });
    }
  } catch (err) {
    // API not ready yet — static options remain
    console.warn('[booking.js] Could not load barbers:', err.message);
  }
})();


/* ════════════════════════════════════════════
   2. LOAD AVAILABLE TIME SLOTS ON DATE CHANGE
   ════════════════════════════════════════════ */
(function initTimeSlots() {
  const dateInput   = getEl('book-date');
  const timeSelect  = getEl('book-time');
  const barberInput = getEl('book-barber');
  if (!dateInput || !timeSelect) return;

  const fetchSlots = async () => {
    const date    = dateInput.value;
    const barber  = barberInput?.value || '';
    if (!date) return;

    try {
      const url = `/api/booking.php?action=slots&date=${date}&barber_id=${barber}`;
      const res = await fetch(url);
      if (!res.ok) return;

      const data = await res.json();
      const slots = data.data || data;

      if (Array.isArray(slots) && slots.length) {
        const current = timeSelect.value;

        // Rebuild options
        timeSelect.innerHTML = '<option value="">— Pilih waktu —</option>';

        slots.forEach(slot => {
          const opt      = document.createElement('option');
          opt.value      = slot.time;
          opt.textContent = slot.time + (slot.available ? '' : ' (Penuh)');
          opt.disabled   = !slot.available;
          if (slot.time === current) opt.selected = true;
          timeSelect.appendChild(opt);
        });
      }
    } catch (err) {
      console.warn('[booking.js] Could not load time slots:', err.message);
    }
  };

  dateInput.addEventListener('change', fetchSlots);
  barberInput?.addEventListener('change', fetchSlots);
})();


/* ════════════════════════════════════════════
   3. FORM SUBMISSION
   ════════════════════════════════════════════ */
(function initBookingForm() {
  const form     = getEl('booking-form');
  const statusEl = getEl('booking-status');
  const submitBtn = getEl('booking-submit');
  if (!form) return;

  const apiEndpoint = form.dataset.apiEndpoint || '/api/booking.php';

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Client-side validation
    const name    = getEl('book-name')?.value.trim();
    const phone   = getEl('book-phone')?.value.trim();
    const service = getEl('book-service')?.value;
    const date    = getEl('book-date')?.value;
    const time    = getEl('book-time')?.value;

    if (!name || !phone || !service || !date || !time) {
      showStatus(statusEl, 'error', 'Lengkapi semua field yang wajib diisi (*).');
      return;
    }

    // Disable button to prevent double-submit
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Memproses...';
    }

    showStatus(statusEl, 'loading', 'Mengirim booking...');

    const payload = {
      customer_name:  name,
      customer_phone: phone,
      service_id:     getEl('book-service')?.value,
      barber_id:      getEl('book-barber')?.value || null,
      booking_date:   date,
      booking_time:   time,
      notes:          getEl('book-notes')?.value.trim() || null,
    };

    try {
      const res = await fetch(apiEndpoint, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
      });

      const data = await res.json();

      if (res.ok && data.success) {
        showStatus(statusEl, 'success',
          `Booking berhasil! Nomor booking Anda: ${data.booking_code || data.id || '—'}. Kami akan menghubungi Anda via WhatsApp.`
        );
        form.reset();
      } else {
        showStatus(statusEl, 'error', data.message || 'Booking gagal. Silakan coba lagi.');
      }
    } catch (err) {
      showStatus(statusEl, 'error', 'Koneksi bermasalah. Periksa internet Anda.');
      console.error('[booking.js]', err);
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Konfirmasi Booking →';
      }
    }
  });
})();