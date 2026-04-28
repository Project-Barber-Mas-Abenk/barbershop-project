<?php
/**
 * Rating Page
 * public/client/rating.php
 */
$active_page = 'rating';
$page_title  = 'Rating — Shift Studio Barbershop';
$extra_css   = ['pages.css'];
$extra_js    = ['rating.js'];

require __DIR__ . '/components/header.php';

// Sample reviews — replace with real DB query via API
$reviews = [
  ['initials' => 'RA', 'name' => 'Rizki Aditya',    'stars' => 5, 'text' => 'Potongannya rapi banget, barbernya juga ramah. Puas!'],
  ['initials' => 'DK', 'name' => 'Dika Kurniawan',   'stars' => 4, 'text' => 'Tempat nyaman, hasil memuaskan. Harga worth it.'],
  ['initials' => 'FD', 'name' => 'Fauzi Darmawan',   'stars' => 5, 'text' => 'Langganan di sini udah lama. Kualitasnya konsisten!'],
  ['initials' => 'PG', 'name' => 'Pranaja Gathan',    'stars' => 5, 'text' => 'Highlight-nya bagus, warna keluar natural sesuai ekspektasi.'],
  ['initials' => 'CA', 'name' => 'Cahyo Adi',         'stars' => 4, 'text' => 'Pelayanan cepat, tidak perlu antri lama kalau booking dulu.'],
  ['initials' => 'BG', 'name' => 'Bagus Ginanjar',    'stars' => 5, 'text' => 'Creambath-nya enak, rambut jadi lebih sehat dan lembut.'],
  ['initials' => 'SA', 'name' => 'Surya Adi',         'stars' => 4, 'text' => 'Lokasi mudah dijangkau, parkir lega. Rekomended!'],
];
?>

<main class="rating-page">

  <!-- ── Header ── -->
  <div class="rating-header reveal">
    <h1>Rating Is In Your Choice</h1>
    <p>Bagikan pengalaman Anda dan bantu pelanggan lain memilih yang terbaik.</p>
  </div>


  <!-- ══════════════════════════════════════════════════
       GIVE A RATING
       ══════════════════════════════════════════════════ -->
  <section class="rating-input-section reveal reveal-delay-1" aria-labelledby="give-rating-heading">
    <h2 id="give-rating-heading" class="sr-only">Give a Rating</h2>

    <!-- Profile — replace with dynamic session data when auth is integrated -->
    <div class="user-avatar" aria-hidden="true" id="user-initials">PG</div>
    <p class="user-name" id="user-display-name">Pranaja Gathan</p>

    <!--
      BACKEND INTEGRATION NOTE:
      data-api-endpoint points to your rating submission endpoint.
      JS in rating.js handles the fetch POST.
    -->
    <div class="rating-card">
      <form
        id="rating-form"
        novalidate
        data-api-endpoint="/api/ratings.php">

        <!-- Star selector -->
        <div class="star-group" role="group" aria-label="Star rating">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="star <?= $i <= 3 ? 'active' : '' ?>"
                  data-value="<?= $i ?>"
                  role="button"
                  tabindex="0"
                  aria-label="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</span>
          <?php endfor; ?>
        </div>
        <input type="hidden" name="rating" id="rating-value" value="3">

        <div class="rating-submit-row">
          <input
            type="text"
            name="comment"
            id="rating-comment"
            class="form-control"
            placeholder="Tulis ulasan Anda..."
            maxlength="280"
            required>
          <button type="submit" class="btn btn-primary">Kirim</button>
        </div>

        <div class="form-message" id="rating-status" role="alert" aria-live="polite"
             style="margin-top: var(--space-4);"></div>
      </form>
    </div>
  </section>


  <!-- ══════════════════════════════════════════════════
       PUBLIC RATINGS
       ══════════════════════════════════════════════════ -->
  <section class="public-ratings" aria-labelledby="public-rating-heading">
    <div class="public-ratings__header reveal">
      <span class="section-heading__label">What Clients Say</span>
      <h2 id="public-rating-heading"
          style="font-size: clamp(1.875rem, 4vw, 3rem); margin-bottom: var(--space-2);">
        Public Ratings
      </h2>

      <!-- Reviewer navigation -->
      <div class="reviewer-nav" role="tablist" aria-label="Reviewer selection" id="reviewer-tabs">
        <?php foreach ($reviews as $i => $r): ?>
          <button
            class="reviewer-nav__btn <?= $i === 3 ? 'active' : '' ?>"
            role="tab"
            aria-selected="<?= $i === 3 ? 'true' : 'false' ?>"
            data-index="<?= $i ?>"
            aria-label="<?= htmlspecialchars($r['name']) ?>">
            <?= htmlspecialchars($r['initials']) ?>
          </button>
        <?php endforeach; ?>
      </div>
      <p class="reviewer-name" id="active-reviewer-name">Pranaja Gathan</p>
    </div>

    <!-- Review cards carousel -->
    <div class="review-carousel" id="review-carousel" role="list">
      <?php foreach ($reviews as $i => $r): ?>
        <article class="review-card <?= $i === 3 ? 'active' : '' ?>"
                 role="listitem"
                 data-index="<?= $i ?>">
          <div class="review-card__header">
            <div class="review-avatar"><?= htmlspecialchars($r['initials']) ?></div>
            <div>
              <p class="review-card__name"><?= htmlspecialchars($r['name']) ?></p>
              <div class="review-stars" aria-label="<?= $r['stars'] ?> stars">
                <?= str_repeat('★', $r['stars']) . str_repeat('☆', 5 - $r['stars']) ?>
              </div>
            </div>
          </div>
          <p class="review-card__text">"<?= htmlspecialchars($r['text']) ?>"</p>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="carousel-controls">
      <button class="btn btn-outline" id="carousel-prev" aria-label="Previous review">← Prev</button>
      <button class="btn btn-outline" id="carousel-next" aria-label="Next review">Next →</button>
    </div>
  </section>

</main>

<?php require __DIR__ . '/components/footer.php'; ?>