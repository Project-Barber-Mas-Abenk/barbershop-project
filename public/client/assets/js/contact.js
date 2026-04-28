/**
 * contact.js — Contact form submission
 * public/client/assets/js/contact.js
 *
 * Posts to /api/contact.php (or data-api-endpoint attribute on the form).
 * Expected response: { success: true|false, message: "..." }
 */

(function initContactForm() {
  const form      = document.getElementById('contact-form');
  const statusEl  = document.getElementById('contact-message-status');
  const submitBtn = document.getElementById('contact-submit');
  if (!form) return;

  const apiEndpoint = form.dataset.apiEndpoint || '/api/contact.php';

  const showStatus = (type, msg) => {
    if (!statusEl) return;
    statusEl.className = `form-message ${type}`;
    statusEl.textContent = msg;
    statusEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const name    = document.getElementById('contact-name')?.value.trim();
    const email   = document.getElementById('contact-email')?.value.trim();
    const message = document.getElementById('contact-message')?.value.trim();

    if (!name || !email || !message) {
      showStatus('error', 'Nama, email, dan pesan wajib diisi.');
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Mengirim...';
    }

    showStatus('loading', 'Mengirim pesan...');

    const payload = {
      name:    name,
      email:   email,
      phone:   document.getElementById('contact-phone')?.value.trim() || null,
      subject: document.getElementById('contact-subject')?.value || null,
      message: message,
    };

    try {
      const res  = await fetch(apiEndpoint, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
      });

      const data = await res.json();

      if (res.ok && data.success) {
        showStatus('success', 'Pesan berhasil dikirim! Kami akan merespons dalam 1×24 jam.');
        form.reset();
      } else {
        showStatus('error', data.message || 'Gagal mengirim. Silakan coba lagi.');
      }
    } catch (err) {
      showStatus('error', 'Koneksi bermasalah. Periksa internet Anda.');
      console.error('[contact.js]', err);
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Message →';
      }
    }
  });
})();