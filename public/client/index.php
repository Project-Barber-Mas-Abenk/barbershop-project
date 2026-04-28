<?php
/**
 * Home Page
 * public/client/index.php
 */
$active_page = 'home';
$page_title  = 'Shift Studio Barbershop';
$extra_css   = ['home.css'];

require __DIR__ . '/components/header.php';
?>

<!-- ══════════════════════════════════════════════════
     HERO
     ══════════════════════════════════════════════════ -->
<main>
<section class="hero" aria-label="Hero">
  <div class="hero__inner">
    <span class="hero__eyebrow">Est. 2018 &nbsp;·&nbsp; Perumnas, Cirebon</span>
    <h1 class="hero__title">Shift<br>Studio</h1>
    <p class="hero__title-ghost" aria-hidden="true">Shift Studio</p>
    <div class="hero__cta">
      <a href="booking.php" class="btn btn-primary btn-lg">
        Book Now &rarr;
      </a>
    </div>
  </div>

  <!-- Founder strip — bottom left of hero -->
  <div class="hero__founder-strip reveal">
    <div class="hero__founder-frame">
      <!-- Replace src with actual founder image when available -->
      <img class="hero__founder-img"
           src="assets/img/founder-placeholder.jpg"
           alt="Mufadhol Abeng, Founder"
           onerror="this.style.background='var(--color-surface-3)';this.removeAttribute('src')">
    </div>
    <div class="hero__founder-info">
      <span class="badge badge-gold">This Is a</span>
      <h3>Founder Shift Studio Barbershop</h3>
      <span class="hero__founder-name">Mufadhol Abeng</span>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════════════
     WHY CHOOSE US
     ══════════════════════════════════════════════════ -->
<section class="why-us section" aria-labelledby="why-heading">
  <div class="section-heading reveal">
    <span class="section-heading__label">Our Work</span>
    <h2 class="section-heading__title" id="why-heading">Why Choose Us?</h2>
    <p class="section-heading__subtitle">
      Precision cuts, premium grooming products, and a team that truly cares
      about the craft — every visit, every time.
    </p>
  </div>

  <!-- Gallery grid — replace # with real image paths when available -->
  <div class="gallery-grid reveal reveal-delay-1" role="list" aria-label="Barbershop gallery">
    <div class="gallery-col">
      <div class="gallery-cell gallery-cell--sm" role="listitem">
        <img src="assets/img/gallery/g1.jpg" alt="Haircut result" loading="lazy"
             onerror="this.parentElement.style.background='var(--color-surface-3)'">
      </div>
      <div class="gallery-cell gallery-cell--lg" role="listitem">
        <img src="assets/img/gallery/g2.jpg" alt="Styling session" loading="lazy"
             onerror="this.parentElement.style.background='var(--color-surface-3)'">
      </div>
    </div>
    <div class="gallery-col">
      <div class="gallery-cell gallery-cell--md" role="listitem">
        <img src="assets/img/gallery/g3.jpg" alt="Barbershop interior" loading="lazy"
             onerror="this.parentElement.style.background='var(--color-surface-3)'">
      </div>
      <div class="gallery-cell gallery-cell--lg" role="listitem">
        <img src="assets/img/gallery/g4.jpg" alt="Coloring service" loading="lazy"
             onerror="this.parentElement.style.background='var(--color-surface-3)'">
      </div>
    </div>
    <div class="gallery-col">
      <div class="gallery-cell gallery-cell--lg" role="listitem">
        <img src="assets/img/gallery/g5.jpg" alt="Premium cut" loading="lazy"
             onerror="this.parentElement.style.background='var(--color-surface-3)'">
      </div>
      <div class="gallery-cell gallery-cell--md" role="listitem">
        <img src="assets/img/gallery/g6.jpg" alt="Beard grooming" loading="lazy"
             onerror="this.parentElement.style.background='var(--color-surface-3)'">
      </div>
    </div>
    <div class="gallery-col">
      <div class="gallery-cell gallery-cell--sm" role="listitem">
        <img src="assets/img/gallery/g7.jpg" alt="Creambath treatment" loading="lazy"
             onerror="this.parentElement.style.background='var(--color-surface-3)'">
      </div>
      <div class="gallery-cell gallery-cell--md" role="listitem">
        <img src="assets/img/gallery/g8.jpg" alt="Highlight service" loading="lazy"
             onerror="this.parentElement.style.background='var(--color-surface-3)'">
      </div>
    </div>
    <div class="gallery-col">
      <div class="gallery-cell gallery-cell--lg" role="listitem">
        <img src="assets/img/gallery/g9.jpg" alt="Shift Studio vibe" loading="lazy"
             onerror="this.parentElement.style.background='var(--color-surface-3)'">
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════════════
     BRANCH
     ══════════════════════════════════════════════════ -->
<section class="branch section" aria-labelledby="branch-heading">
  <div class="branch__inner">
    <div class="reveal">
      <p class="branch__label">Our</p>
      <h2 class="branch__title" id="branch-heading">BRANCH</h2>
      <p class="branch__sub">Shift Studio Barbershop</p>

      <h3 class="branch__location-name">PERUMNAS</h3>
      <p class="branch__address">
        Jl. Perumnas, Cirebon, Jawa Barat<br>
        Buka setiap hari &nbsp;·&nbsp; 09.00 – 21.00 WIB
      </p>

      <p style="font-size: var(--text-xs); color: var(--color-text-muted); margin-bottom: var(--space-2); text-transform: uppercase; letter-spacing: .08em;">
        Available Services
      </p>
      <div class="branch__service-tags">
        <span class="badge badge-outline">Haircut</span>
        <span class="badge badge-outline">Coloring</span>
        <span class="badge badge-outline">Haircut Booking</span>
        <span class="badge badge-outline">Creambath</span>
      </div>

      <div class="branch__cta">
        <a href="booking.php" class="btn btn-primary">Book Here</a>
        <a href="about.php#map"  class="btn btn-outline">See On Map</a>
      </div>
    </div>

    <!-- Map -->
    <div class="branch__map-side reveal reveal-delay-2">
      <div class="branch__map-frame">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d819.7121872707734!2d108.55990861059631!3d-6.746253034105004!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6f1d744ef48d81%3A0x1719de0b1af8376f!2sShift%20studio!5e0!3m2!1sid!2sid!4v1775063523978!5m2!1sid!2sid"
          title="Shift Studio Barbershop location"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════════════
     PRICING
     ══════════════════════════════════════════════════ -->
<section class="pricing section" aria-labelledby="pricing-heading">
  <div class="section-heading reveal">
    <span class="section-heading__label">Services &amp; Pricing</span>
    <h2 class="section-heading__title" id="pricing-heading">Available Services</h2>
  </div>

  <div class="pricing__grid" role="list">
    <?php
    $services = [
      ['Haircut',          '30K'],
      ['Haircut Booking',  '50K'],
      ['Hairwash',         '10K'],
      ['Coloring',         '100K – 300K'],
      ['Creambath',        '40K'],
      ['Highlight',        '100K – 300K'],
    ];
    foreach ($services as $i => [$name, $price]):
    ?>
    <article class="price-card reveal reveal-delay-<?= ($i % 4) + 1 ?>" role="listitem">
      <p class="price-card__name"><?= htmlspecialchars($name) ?></p>
      <h3 class="price-card__price"><?= htmlspecialchars($price) ?></h3>
    </article>
    <?php endforeach; ?>
  </div>
</section>
</main>

<?php require __DIR__ . '/components/footer.php'; ?>