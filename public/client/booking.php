<?php
/**
 * Booking Page (NEW)
 * public/client/booking.php
 *
 * This page did not exist in the original frontend — it is the destination
 * for all "Book Now" and "Book Here" CTAs. The form is wired to the existing
 * /api/booking.php endpoint via JS fetch.
 */
$active_page = '';   // No nav link is active — booking is a CTA destination
$page_title  = 'Book an Appointment — Shift Studio Barbershop';
$extra_css   = ['pages.css'];
$extra_js    = ['booking.js'];

require __DIR__ . '/components/header.php';
?>

<main class="booking-page">
  <div class="booking-page__inner">

    <div class="page-hero__title-box reveal">
      <h1>BOOK NOW</h1>
    </div>
    <p class="page-hero__desc reveal reveal-delay-1"
       style="margin-bottom: var(--space-8);">
      Pilih layanan, barber, tanggal, dan waktu yang sesuai.
      Tim kami siap melayani Anda.
    </p>

    <!--
      BACKEND INTEGRATION NOTE:
      All name attributes match the expected fields in /api/booking.php.
      The JS in booking.js handles:
        - Fetching available barbers  → GET /api/barbers.php
        - Fetching available services → GET /api/services.php
        - Checking time slots        → GET /api/booking.php?date=&barber_id=
        - Submitting the booking     → POST /api/booking.php
      Replace the static <option> lists with dynamic fetches once the API
      endpoints are confirmed.
    -->
    <form
      class="booking-form reveal reveal-delay-2"
      id="booking-form"
      novalidate
      data-api-endpoint="/api/booking.php">

      <!-- Row: Name + Phone -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="book-name">Full Name *</label>
          <input
            type="text"
            id="book-name"
            name="customer_name"
            class="form-control"
            placeholder="Budi Santoso"
            required
            autocomplete="name">
        </div>

        <div class="form-group">
          <label class="form-label" for="book-phone">WhatsApp / Phone *</label>
          <input
            type="tel"
            id="book-phone"
            name="customer_phone"
            class="form-control"
            placeholder="+62 812-3456-7890"
            required
            autocomplete="tel">
        </div>
      </div>

      <!-- Service -->
      <div class="form-group">
        <label class="form-label" for="book-service">Service *</label>
        <select id="book-service" name="service_id" class="form-control" required>
          <option value="">— Pilih layanan —</option>
          <!-- TODO: populate dynamically from GET /api/services.php -->
          <option value="1">Haircut — Rp 30.000</option>
          <option value="2">Haircut Booking — Rp 50.000</option>
          <option value="3">Hairwash — Rp 10.000</option>
          <option value="4">Coloring — Rp 100.000 – 300.000</option>
          <option value="5">Creambath — Rp 40.000</option>
          <option value="6">Highlight — Rp 100.000 – 300.000</option>
        </select>
      </div>

      <!-- Barber -->
      <div class="form-group">
        <label class="form-label" for="book-barber">Barber</label>
        <select id="book-barber" name="barber_id" class="form-control">
          <option value="">— Pilih barber (opsional) —</option>
          <!-- TODO: populate dynamically from GET /api/barbers.php -->
        </select>
        <small style="color: var(--color-text-muted); font-size: var(--text-xs); margin-top: 4px;">
          Kosongkan jika tidak ada preferensi
        </small>
      </div>

      <!-- Row: Date + Time -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="book-date">Date *</label>
          <input
            type="date"
            id="book-date"
            name="booking_date"
            class="form-control"
            required
            min="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="book-time">Time *</label>
          <select id="book-time" name="booking_time" class="form-control" required>
            <option value="">— Pilih waktu —</option>
            <?php
            // Generate time slots 09:00 – 20:30 in 30-min increments
            $start = strtotime('09:00');
            $end   = strtotime('20:30');
            for ($t = $start; $t <= $end; $t += 1800):
            ?>
              <option value="<?= date('H:i', $t) ?>"><?= date('H:i', $t) ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </div>

      <!-- Notes -->
      <div class="form-group">
        <label class="form-label" for="book-notes">Additional Notes</label>
        <textarea
          id="book-notes"
          name="notes"
          class="form-control"
          placeholder="Contoh: Ingin hasil rambut lebih pendek di bagian sisi..."
          rows="3">
        </textarea>
      </div>

      <!-- Status -->
      <div class="form-message" id="booking-status" role="alert" aria-live="polite"></div>

      <!-- Submit -->
      <button type="submit" class="btn btn-primary btn-lg booking-form__submit" id="booking-submit">
        Konfirmasi Booking &rarr;
      </button>
    </form>

  </div>
</main>

<?php require __DIR__ . '/components/footer.php'; ?>