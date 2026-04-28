<?php
/**
 * Membership Page
 * public/client/membership.php
 */
$active_page = 'membership';
$page_title  = 'Membership — Shift Studio Barbershop';
$extra_css   = ['pages.css'];

require __DIR__ . '/components/header.php';
?>

<main class="membership-page">
  <div class="membership-page__inner">

    <!-- Title -->
    <div class="page-hero__title-box reveal" style="margin-bottom: var(--space-6);">
      <h1>MEMBERSHIP</h1>
    </div>
    <p class="page-hero__desc reveal reveal-delay-1" style="margin-bottom: var(--space-12);">
      Bergabunglah sebagai member Shift Studio dan nikmati berbagai keuntungan
      eksklusif — diskon spesial, prioritas booking, dan banyak lagi.
    </p>

    <!-- ── Membership Card ── -->
    <!--
      BACKEND INTEGRATION NOTE:
      Replace the empty .card-field placeholders with actual member data
      fetched from the session / API:
        $member_name = $_SESSION['name'] ?? '';
        $member_no   = $_SESSION['member_no'] ?? '';
        $member_since = $_SESSION['member_since'] ?? '';
    -->
    <div class="membership-card reveal reveal-delay-2" aria-label="Membership card preview">
      <!-- Watermark layer -->
      <div class="membership-card__watermark" aria-hidden="true">
        <img src="assets/img/Logo.png" alt="">
        <p class="membership-card__watermark-text">SHIFT STUDIO BARBERSHOP</p>
      </div>

      <!-- Foreground content -->
      <div class="membership-card__content">
        <div class="membership-card__top">
          <div>
            <div class="card-field card-field--name"
                 aria-label="Member name placeholder">
            </div>
            <p class="card-label">NAME</p>
            <h2 class="membership-card__title">MEMBERSHIP<br>CARD</h2>
          </div>
          <div class="membership-card__logo">
            <img src="assets/img/Logo.png" alt="Shift Studio">
          </div>
        </div>

        <div class="membership-card__bottom-fields">
          <div>
            <div class="card-field card-field--no"
                 aria-label="Member number placeholder">
            </div>
            <p class="card-label">MEMBER NO</p>
          </div>
          <div>
            <div class="card-field card-field--since"
                 aria-label="Member since placeholder">
            </div>
            <p class="card-label">SINCE</p>
          </div>
        </div>

        <p class="membership-card__tagline">
          JOIN MEMBERSHIP NOW &nbsp;★&nbsp; SHIFTSTUDIO.ID
        </p>
      </div>
    </div>

    <!-- ── Benefits ── -->
    <div class="reveal reveal-delay-2"
         style="max-width: 600px; margin: var(--space-16) auto 0; text-align: center;">
      <span class="section-heading__label">Member Benefits</span>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-top: var(--space-6); text-align: left;">
        <?php
        $benefits = [
          ['🎯', 'Diskon 10%',          'Untuk semua layanan setiap kunjungan'],
          ['⚡', 'Prioritas Booking',    'Antrian diutamakan untuk member'],
          ['🎁', 'Hadiah Ulang Tahun',   'Layanan gratis di bulan ulang tahun'],
          ['📊', 'Riwayat Kunjungan',    'Lacak semua aktivitas grooming Anda'],
        ];
        foreach ($benefits as [$icon, $title, $desc]):
        ?>
        <div style="background: var(--color-surface-2); border: 1px solid var(--color-border);
                    border-radius: var(--radius-lg); padding: var(--space-5);">
          <div style="font-size: 1.5rem; margin-bottom: var(--space-2)"><?= $icon ?></div>
          <p style="font-weight: 600; color: var(--color-text-primary);
                    margin-bottom: var(--space-1); font-size: var(--text-sm);"><?= $title ?></p>
          <p style="font-size: var(--text-xs); color: var(--color-text-muted); line-height: 1.5;"><?= $desc ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ── CTA ── -->
    <div class="membership-cta reveal reveal-delay-3">
      <p>
        Daftar membership sekarang dan rasakan perbedaannya.
        Tersedia untuk semua pelanggan Shift Studio.
      </p>
      <!--
        BACKEND INTEGRATION NOTE:
        Link to your membership registration flow.
        If the user is already logged in, you could change this to a
        direct enroll endpoint: /api/membership/enroll.php
      -->
      <a href="../pages/register.php?redirect=membership"
         class="btn btn-primary btn-lg">
        Daftar Membership &rarr;
      </a>
    </div>

  </div>
</main>

<?php require __DIR__ . '/components/footer.php'; ?>