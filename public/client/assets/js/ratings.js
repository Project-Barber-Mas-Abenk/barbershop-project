/**
 * rating.js — Rating page interactions
 * public/client/assets/js/rating.js
 *
 * Responsibilities:
 *   1. Interactive star picker
 *   2. Reviewer navigation tabs (carousel)
 *   3. Form submission → POST /api/ratings.php
 */

/* ════════════════════════════════════════════
   1. STAR PICKER
   ════════════════════════════════════════════ */
(function initStarPicker() {
  const stars      = document.querySelectorAll('.star-group .star');
  const ratingInput = document.getElementById('rating-value');
  if (!stars.length || !ratingInput) return;

  let currentRating = parseInt(ratingInput.value, 10) || 3;

  const render = (highlighted) => {
    stars.forEach((s, i) => {
      s.classList.toggle('active', i < highlighted);
    });
  };

  // Click to set
  stars.forEach((star, i) => {
    star.addEventListener('click', () => {
      currentRating = i + 1;
      ratingInput.value = currentRating;
      render(currentRating);
    });

    // Keyboard
    star.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        currentRating = i + 1;
        ratingInput.value = currentRating;
        render(currentRating);
      }
    });

    // Hover preview
    star.addEventListener('mouseenter', () => render(i + 1));
    star.addEventListener('mouseleave', () => render(currentRating));
  });

  render(currentRating);
})();


/* ════════════════════════════════════════════
   2. REVIEWER TABS + CAROUSEL
   ════════════════════════════════════════════ */
(function initReviewerCarousel() {
  const tabs        = document.querySelectorAll('#reviewer-tabs .reviewer-nav__btn');
  const cards       = document.querySelectorAll('.review-card');
  const nameDisplay = document.getElementById('active-reviewer-name');
  const carousel    = document.getElementById('review-carousel');
  const prevBtn     = document.getElementById('carousel-prev');
  const nextBtn     = document.getElementById('carousel-next');

  if (!tabs.length || !cards.length) return;

  let activeIndex = 3; // default to 4th reviewer (PG)

  const setActive = (index) => {
    activeIndex = ((index % tabs.length) + tabs.length) % tabs.length;

    // Update tabs
    tabs.forEach((t, i) => {
      const isActive = i === activeIndex;
      t.classList.toggle('active', isActive);
      t.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    // Update cards
    cards.forEach((c, i) => {
      c.classList.toggle('active', i === activeIndex);
    });

    // Update name label
    if (nameDisplay) {
      nameDisplay.textContent = tabs[activeIndex].getAttribute('aria-label') || '';
    }

    // Scroll card into view
    if (carousel && cards[activeIndex]) {
      const card = cards[activeIndex];
      const offset = card.offsetLeft - carousel.offsetWidth / 2 + card.offsetWidth / 2;
      carousel.scrollTo({ left: offset, behavior: 'smooth' });
    }
  };

  tabs.forEach((tab, i) => {
    tab.addEventListener('click', () => setActive(i));
  });

  prevBtn?.addEventListener('click', () => setActive(activeIndex - 1));
  nextBtn?.addEventListener('click', () => setActive(activeIndex + 1));

  // Keyboard on carousel
  document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft')  setActive(activeIndex - 1);
    if (e.key === 'ArrowRight') setActive(activeIndex + 1);
  });

  setActive(activeIndex);
})();


/* ════════════════════════════════════════════
   3. RATING FORM SUBMISSION
   ════════════════════════════════════════════ */
(function initRatingForm() {
  const form      = document.getElementById('rating-form');
  const statusEl  = document.getElementById('rating-status');
  if (!form) return;

  const apiEndpoint = form.dataset.apiEndpoint || '/api/ratings.php';

  const showStatus = (type, msg) => {
    if (!statusEl) return;
    statusEl.className = `form-message ${type}`;
    statusEl.textContent = msg;
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const comment = document.getElementById('rating-comment')?.value.trim();
    const rating  = document.getElementById('rating-value')?.value;

    if (!comment) {
      showStatus('error', 'Tulis ulasan terlebih dahulu.');
      return;
    }

    showStatus('loading', 'Mengirim ulasan...');

    const payload = {
      rating:  parseInt(rating, 10),
      comment: comment,
    };

    try {
      const res = await fetch(apiEndpoint, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
      });

      const data = await res.json();

      if (res.ok && data.success) {
        showStatus('success', 'Ulasan berhasil dikirim! Terima kasih.');
        form.reset();
        // Re-render stars to default
        document.querySelectorAll('.star-group .star').forEach((s, i) => {
          s.classList.toggle('active', i < 3);
        });
        document.getElementById('rating-value').value = 3;
      } else {
        showStatus('error', data.message || 'Gagal mengirim ulasan. Coba lagi.');
      }
    } catch (err) {
      showStatus('error', 'Koneksi bermasalah. Periksa internet Anda.');
      console.error('[rating.js]', err);
    }
  });
})();