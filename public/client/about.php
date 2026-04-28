<?php
/**
 * About Page
 * public/client/about.php
 */
$active_page = 'about';
$page_title  = 'About Us — Shift Studio Barbershop';
$extra_css   = ['pages.css'];

require __DIR__ . '/components/header.php';
?>

<main>

<!-- ══════════════════════════════════════════════════
     ABOUT HERO
     ══════════════════════════════════════════════════ -->
<section class="about-hero" aria-label="About hero">
  <div class="about-hero__img-side reveal">
    <div class="arch-frame">
      <img
        src="https://images.fresha.com/locations/location-profile-images/2522149/4581012/f313c44c-dd7b-4b95-ad47-3a14ec93607f-URBANBLADESBARBERSHOP-ID-Bali-Bali-Fresha.jpg"
        alt="Shift Studio Barbershop interior"
        loading="eager">
    </div>
  </div>

  <div class="about-hero__text reveal reveal-delay-2">
    <span class="eyebrow">Est. 2018</span>
    <h1>Where <em>Great,</em><br>Style <em>Begins.</em></h1>
    <p style="margin-top: 1.5rem; font-size: 1rem; color: var(--color-text-secondary); max-width: 420px; line-height: 1.8;">
      Shift Studio Barbershop is more than a haircut — it is an experience built
      on skill, trust, and a passion for clean, confident style.
    </p>
  </div>
</section>


<!-- ══════════════════════════════════════════════════
     FOUNDER
     ══════════════════════════════════════════════════ -->
<section class="founder-section section" aria-labelledby="founder-heading">
  <!-- Faint background photo -->
  <div class="founder-section__bg"
       style="background-image: url('https://images.fresha.com/locations/location-profile-images/2522149/4581012/f313c44c-dd7b-4b95-ad47-3a14ec93607f-URBANBLADESBARBERSHOP-ID-Bali-Bali-Fresha.jpg');"
       aria-hidden="true">
  </div>

  <div class="container">
    <div class="founder-card reveal">
      <div class="founder-card__img">
        <!-- Replace with actual founder photo -->
        <img src="assets/img/founder-placeholder.jpg"
             alt="Mufadhol Abeng, Founder"
             onerror="this.style.background='var(--color-surface-3)';this.removeAttribute('src')">
        <span class="founder-card__name-stamp">Abeng</span>
      </div>

      <div class="founder-card__info">
        <span class="badge badge-gold">This Is a</span>
        <h2 id="founder-heading">Founder Shift Studio Barbershop</h2>
        <p>
          Mufadhol Abeng memulai Shift Studio dengan satu keyakinan sederhana:
          setiap orang berhak mendapatkan potongan rambut yang tepat, oleh
          tangan yang terampil, di tempat yang nyaman. Sejak 2018, visi itu
          telah tumbuh menjadi barbershop dengan reputasi terbaik di Perumnas.
        </p>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════════════
     STORY & STORES
     ══════════════════════════════════════════════════ -->
<div class="container">
  <div class="content-rows">

    <!-- Our Story -->
    <div class="content-row reveal">
      <div class="content-row__text">
        <h2>Our Story</h2>
        <p>
          Berawal dari sebuah kursi dan tekad yang kuat, Shift Studio hadir untuk
          menghadirkan pengalaman grooming berkelas ke tengah komunitas. Selama
          bertahun-tahun kami terus berkembang — memperbarui teknik, melatih
          tim, dan memastikan setiap pelanggan keluar dengan percaya diri.
        </p>
      </div>
      <div class="content-row__img">
        <img
          src="https://images.fresha.com/locations/location-profile-images/2522149/4581012/f313c44c-dd7b-4b95-ad47-3a14ec93607f-URBANBLADESBARBERSHOP-ID-Bali-Bali-Fresha.jpg"
          alt="Shift Studio barbershop suasana"
          loading="lazy">
      </div>
    </div>

    <!-- Our Stores -->
    <div class="content-row content-row--reverse reveal">
      <div class="content-row__text">
        <h2>Our Store</h2>
        <p>
          Shift Studio berlokasi strategis di kawasan Perumnas, Cirebon —
          mudah dijangkau dan selalu ramai dengan pelanggan setia. Fasilitas
          kami dirancang nyaman: AC, sound system, dan area tunggu yang
          bersih supaya waktu Anda terasa berharga.
        </p>
      </div>
      <div class="content-row__img">
        <img
          src="https://images.fresha.com/locations/location-profile-images/2522149/4581012/f313c44c-dd7b-4b95-ad47-3a14ec93607f-URBANBLADESBARBERSHOP-ID-Bali-Bali-Fresha.jpg"
          alt="Shift Studio store front"
          loading="lazy">
      </div>
    </div>

  </div>
</div>


<!-- ══════════════════════════════════════════════════
     MAP
     ══════════════════════════════════════════════════ -->
<section class="map-section section" id="map" aria-labelledby="map-heading">
  <div class="container">
    <h2 id="map-heading" class="reveal">Location Shift Studio</h2>
    <div class="map-frame reveal reveal-delay-1">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d819.7121872707734!2d108.55990861059631!3d-6.746253034105004!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6f1d744ef48d81%3A0x1719de0b1af8376f!2sShift%20studio!5e0!3m2!1sid!2sid!4v1775063523978!5m2!1sid!2sid"
        title="Shift Studio location map"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>
  </div>
</section>

</main>

<?php require __DIR__ . '/components/footer.php'; ?>