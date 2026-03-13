// Library – minimal JS (e.g. confirm delete)
document.querySelectorAll('[data-confirm]').forEach(function (el) {
  el.addEventListener('click', function (e) {
    if (!confirm(this.getAttribute('data-confirm'))) e.preventDefault();
  });
});
