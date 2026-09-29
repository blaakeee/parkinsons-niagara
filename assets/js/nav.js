// Shared behaviour across pages. Kept tiny and dependency-free.

// Close mobile menu on Escape
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    var nav = document.getElementById('nav');
    if (nav && nav.classList.contains('open')) {
      nav.classList.remove('open');
      var btn = nav.querySelector('.menu-btn');
      if (btn) { btn.setAttribute('aria-expanded', 'false'); btn.focus(); }
    }
  }
});

// Mark current page in nav for orientation
(function () {
  var here = location.pathname.replace(/\/$/, '') || '/index.html';
  document.querySelectorAll('.navlinks a').forEach(function (a) {
    var path = a.getAttribute('href');
    if (path === here || (here === '/' && path === '/index.html')) {
      a.setAttribute('aria-current', 'page');
      a.style.color = 'var(--river)';
    }
  });
})();

