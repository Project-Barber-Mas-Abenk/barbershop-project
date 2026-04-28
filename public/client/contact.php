<?php
/**
 * Contact Page
 * public/client/contact.php
 */
$active_page = 'contact';
$page_title  = 'Contact Us — Shift Studio Barbershop';
$extra_css   = ['pages.css'];
$extra_js    = ['contact.js'];

require __DIR__ . '/components/header.php';
?>

<main>

<div class="contact-layout">

  <!-- ── Info Panel ── -->
  <div class="contact-info-panel">
    <div class="page-hero__title-box reveal">
      <h1>CONTACT US</h1>
    </div>

    <p class="page-hero__desc reveal reveal-delay-1">
      Untuk pertanyaan, kolaborasi, atau sekadar menyapa — kami selalu senang
      mendengar dari Anda.
    </p>

    <div class="contact-info-list reveal reveal-delay-2">
      <div class="contact-info-item">
        <label>WhatsApp Customer Service</label>
        <p>
          <a href="https://wa.me/6282652762891" target="_blank" rel="noopener"
             style="color: var(--color-text-primary); transition: color .2s;">
            +62 826-5276-2891
          </a>
        </p>
      </div>

      <div class="contact-info-item">
        <label>Email</label>
        <p>
          <a href="mailto:hello@shiftstudio.id"
             style="color: var(--color-text-primary); transition: color .2s;">
            hello@shiftstudio.id
          </a>
        </p>
      </div>

      <div class="contact-info-item">
        <label>Address</label>
        <p>
          Jl. Perumnas, Cirebon<br>
          Jawa Barat, Indonesia
        </p>
      </div>

      <div class="contact-info-item">
        <label>Hours</label>
        <p>
          Senin – Minggu &nbsp;·&nbsp; 09.00 – 21.00 WIB
        </p>
      </div>
    </div>
  </div>

  <!-- ── Form Panel ── -->
  <div class="contact-form-panel">
    <h2 class="reveal">Send Us a Message</h2>

    <!--
      BACKEND INTEGRATION NOTE:
      This form posts to /api/contact.php (or your preferred endpoint).
      Set action="" to use the current page and handle server-side,
      or leave data-api-endpoint for the JS fetch handler in contact.js.
    -->
    <form
      class="contact-form reveal reveal-delay-1"
      id="contact-form"
      novalidate
      data-api-endpoint="/api/contact.php">

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="contact-name">Full Name *</label>
          <input
            type="text"
            id="contact-name"
            name="name"
            class="form-control"
            placeholder="Budi Santoso"
            required
            autocomplete="name">
        </div>

        <div class="form-group">
          <label class="form-label" for="contact-phone">Phone</label>
          <input
            type="tel"
            id="contact-phone"
            name="phone"
            class="form-control"
            placeholder="+62 812-3456-7890"
            autocomplete="tel">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="contact-email">Email Address *</label>
        <input
          type="email"
          id="contact-email"
          name="email"
          class="form-control"
          placeholder="budi@email.com"
          required
          autocomplete="email">
      </div>

      <div class="form-group">
        <label class="form-label" for="contact-subject">Subject</label>
        <select id="contact-subject" name="subject" class="form-control">
          <option value="">— Pilih topik —</option>
          <option value="booking">Booking &amp; Reservasi</option>
          <option value="membership">Membership</option>
          <option value="collab">Kolaborasi</option>
          <option value="other">Lainnya</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="contact-message">Message *</label>
        <textarea
          id="contact-message"
          name="message"
          class="form-control"
          placeholder="Tulis pesan Anda di sini..."
          required
          rows="5">
        </textarea>
      </div>

      <!-- Status message -->
      <div class="form-message" id="contact-message-status" role="alert" aria-live="polite"></div>

      <button type="submit" class="btn btn-primary btn-lg" id="contact-submit">
        Send Message &rarr;
      </button>
    </form>
  </div>

</div>

</main>

<?php require __DIR__ . '/components/footer.php'; ?>