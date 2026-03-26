// Library – minimal JS

function initMobileNav() {
  var header = document.getElementById('site-header');
  var btn = document.getElementById('nav-toggle-btn');
  var nav = document.getElementById('site-nav');
  if (!header || !btn) return;

  function isMenuOpen() {
    return header.classList.contains('menu-open');
  }
  function setMenuOpen(open) {
    header.classList.toggle('menu-open', !!open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  function toggleMenu() {
    setMenuOpen(!isMenuOpen());
  }

  function onToggleClick(e) {
    e.preventDefault();
    e.stopPropagation();
    toggleMenu();
  }

  btn.addEventListener('click', onToggleClick);

  // Close when a nav link is clicked
  if (nav) {
    nav.addEventListener('click', function (e) {
      if (e.target && e.target.tagName === 'A') setMenuOpen(false);
    });
  }

  // Close when clicking/tapping outside (not on the button)
  document.addEventListener('click', function (e) {
    if (!isMenuOpen()) return;
    var t = e.target;
    if (t && (t === btn || btn.contains(t))) return;
    if (t && header.contains(t)) return;
    setMenuOpen(false);
  });
  document.addEventListener('touchend', function (e) {
    if (!isMenuOpen()) return;
    var t = e.target;
    if (t && (t === btn || btn.contains(t))) return;
    if (t && header.contains(t)) return;
    setMenuOpen(false);
  }, { passive: true });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initMobileNav);
} else {
  initMobileNav();
}

// Landing: features slider (mobile carousel)
(function () {
  function initSlider(root) {
    var track = root.querySelector('[data-slider-track]');
    if (!track) return;

    var prevBtn = root.querySelector('[data-slider-prev]');
    var nextBtn = root.querySelector('[data-slider-next]');
    var dotsWrap = root.querySelector('[data-slider-dots]');
    var slides = Array.prototype.slice.call(track.children || []).filter(function (el) {
      return el && el.classList && el.classList.contains('landing-feature');
    });
    if (slides.length < 2) return;

    var active = 0;
    var timer = null;

    function getSlideLeft(i) {
      return slides[i].offsetLeft;
    }
    function scrollToSlide(i, smooth) {
      active = (i + slides.length) % slides.length;
      track.scrollTo({ left: getSlideLeft(active), behavior: smooth ? 'smooth' : 'auto' });
      updateDots();
    }
    function updateDots() {
      if (!dotsWrap) return;
      var dots = dotsWrap.querySelectorAll('.slider-dot');
      dots.forEach(function (d, idx) {
        d.classList.toggle('is-active', idx === active);
      });
    }
    function buildDots() {
      if (!dotsWrap) return;
      dotsWrap.innerHTML = '';
      slides.forEach(function (_, idx) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'slider-dot' + (idx === 0 ? ' is-active' : '');
        b.setAttribute('aria-label', 'Go to feature ' + (idx + 1));
        b.addEventListener('click', function () {
          stopAuto();
          scrollToSlide(idx, true);
          startAuto();
        });
        dotsWrap.appendChild(b);
      });
    }
    function startAuto() {
      stopAuto();
      timer = window.setInterval(function () {
        scrollToSlide(active + 1, true);
      }, 4500);
    }
    function stopAuto() {
      if (timer) window.clearInterval(timer);
      timer = null;
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { stopAuto(); scrollToSlide(active - 1, true); startAuto(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { stopAuto(); scrollToSlide(active + 1, true); startAuto(); });

    // Keep active index in sync when user swipes
    var raf = null;
    track.addEventListener('scroll', function () {
      if (raf) cancelAnimationFrame(raf);
      raf = requestAnimationFrame(function () {
        var best = 0;
        var bestDist = Infinity;
        var left = track.scrollLeft;
        for (var i = 0; i < slides.length; i++) {
          var d = Math.abs(getSlideLeft(i) - left);
          if (d < bestDist) { bestDist = d; best = i; }
        }
        active = best;
        updateDots();
      });
    }, { passive: true });

    // Pause on hover/focus
    root.addEventListener('mouseenter', stopAuto);
    root.addEventListener('mouseleave', startAuto);
    root.addEventListener('focusin', stopAuto);
    root.addEventListener('focusout', startAuto);

    buildDots();
    // Initial scroll after layout (desktop and mobile)
    function initScroll() {
      track.scrollLeft = 0;
      active = 0;
      updateDots();
    }
    if (typeof requestAnimationFrame !== 'undefined') {
      requestAnimationFrame(initScroll);
    } else {
      initScroll();
    }
    startAuto();
  }

  function initAll() {
    document.querySelectorAll('[data-slider=\"features\"]').forEach(initSlider);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(function (el) {
  el.addEventListener('click', function (e) {
    if (!confirm(this.getAttribute('data-confirm'))) e.preventDefault();
  });
});
