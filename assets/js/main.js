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

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(function (el) {
  el.addEventListener('click', function (e) {
    if (!confirm(this.getAttribute('data-confirm'))) e.preventDefault();
  });
});
