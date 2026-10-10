// MEDEX — JS del sitio
// Marca como activo el link de header/footer/bottom-nav que corresponde a la página actual.
(function () {
  var current = location.pathname.split('/').pop() || 'index.html';

  document.querySelectorAll('.site-header__nav a, .site-footer__nav a, .bottom-nav a').forEach(function (link) {
    if (link.getAttribute('href') === current) {
      link.classList.add('is-active');
      link.setAttribute('aria-current', 'page');
    }
  });
})();

// Header sticky (Design System v2): agrega .is-scrolled a .site-header
// cuando se bajó de scrollY 0, para la sombra definida en components.css.
(function () {
  var header = document.querySelector('.site-header');
  if (!header) return;

  var ticking = false;

  function update() {
    header.classList.toggle('is-scrolled', window.scrollY > 0);
    ticking = false;
  }

  window.addEventListener('scroll', function () {
    if (!ticking) {
      window.requestAnimationFrame(update);
      ticking = true;
    }
  }, { passive: true });

  update();
})();

// Hero (Home) — carrusel de imagen única con crossfade + stepper de puntos.
// Autoplay con pausa en hover/focus; sin autoplay si prefers-reduced-motion.
(function () {
  var frame = document.querySelector('.hero__frame');
  if (!frame) return;

  var images = Array.prototype.slice.call(frame.querySelectorAll('.hero__frame-image'));
  var dots = Array.prototype.slice.call(document.querySelectorAll('.hero__dot'));
  if (!images.length || !dots.length) return;

  var current = 0;
  var intervalId = null;
  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function goTo(index) {
    if (index === current) return;
    images[current].classList.remove('is-active');
    dots[current].classList.remove('is-active');
    dots[current].setAttribute('aria-pressed', 'false');

    current = index;

    images[current].classList.add('is-active');
    dots[current].classList.add('is-active');
    dots[current].setAttribute('aria-pressed', 'true');
  }

  function next() {
    goTo((current + 1) % images.length);
  }

  function start() {
    if (prefersReducedMotion || intervalId) return;
    intervalId = window.setInterval(next, 5000);
    media.classList.remove('is-paused');
  }

  function stop() {
    if (intervalId) {
      window.clearInterval(intervalId);
      intervalId = null;
    }
    media.classList.add('is-paused');
  }

  dots.forEach(function (dot, index) {
    dot.addEventListener('click', function () {
      goTo(index);
      stop();
      start();
    });
  });

  var media = document.querySelector('.hero__media');
  media.addEventListener('mouseenter', stop);
  media.addEventListener('mouseleave', start);
  media.addEventListener('focusin', stop);
  media.addEventListener('focusout', start);

  start();
})();

// Scroll-reveal (Fase 5, motion) — agrega .is-visible a cada <section>
// (salvo la primera, el hero) cuando entra en viewport. Sin autoplay/sin
// transición si prefers-reduced-motion o si no hay IntersectionObserver.
(function () {
  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var sections = document.querySelectorAll('main > section');
  if (sections.length <= 1) return;

  var targets = Array.prototype.slice.call(sections, 1);

  if (prefersReducedMotion || !('IntersectionObserver' in window)) {
    return;
  }

  targets.forEach(function (section) {
    section.classList.add('reveal');
  });

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.15,
    rootMargin: '0px 0px -40px 0px'
  });

  targets.forEach(function (section) {
    observer.observe(section);
  });
})();
