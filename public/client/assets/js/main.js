/**
 * main.js — Shared client-side logic
 * public/client/assets/js/main.js
 */

/* ════════════════════════════════════════════
   NAVBAR
   ════════════════════════════════════════════ */
(function initNavbar() {
  const navbar  = document.getElementById('navbar');
  const toggle  = document.getElementById('nav-toggle');
  const navMenu = document.getElementById('nav-menu');
  const overlay = document.getElementById('nav-overlay');

  if (!navbar) return;

  // Scroll: add .scrolled class
  const onScroll = () => {
    navbar.classList.toggle('scrolled', window.scrollY > 20);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Toggle mobile menu
  const openMenu = () => {
    toggle.classList.add('is-active');
    toggle.setAttribute('aria-expanded', 'true');
    navMenu.classList.add('is-open');
    overlay.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  };

  const closeMenu = () => {
    toggle.classList.remove('is-active');
    toggle.setAttribute('aria-expanded', 'false');
    navMenu.classList.remove('is-open');
    overlay.classList.remove('is-open');
    document.body.style.overflow = '';
  };

  toggle?.addEventListener('click', () => {
    toggle.classList.contains('is-active') ? closeMenu() : openMenu();
  });

  overlay?.addEventListener('click', closeMenu);

  // Close on ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenu();
  });

  // Close when a nav link is clicked (mobile)
  navMenu?.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', closeMenu);
  });
})();


/* ════════════════════════════════════════════
   SCROLL REVEAL
   ════════════════════════════════════════════ */
(function initReveal() {
  const targets = document.querySelectorAll('.reveal');
  if (!targets.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.12,
    rootMargin: '0px 0px -40px 0px',
  });

  targets.forEach(el => observer.observe(el));
})();