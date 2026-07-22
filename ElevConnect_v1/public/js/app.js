/* ===========================================================================
   ElevConnect — interactions de la page d'accueil (repris de la maquette).
   Compteurs animés, onglets 'acteurs', carrousel d'annonces, révélations au
   scroll, menu mobile. Les statistiques (statEleveurs / statAnnonces) sont
   injectées côté serveur par Blade — voir data-* sur .hero-stats.
   =========================================================================== */
// Header shadow on scroll
const header = document.getElementById('siteHeader');
window.addEventListener('scroll', () => {
  header.classList.toggle('scrolled', window.scrollY > 10);
});

// Garde la hauteur réelle du header à jour (le menu déroulant s'ancre juste en dessous)
function syncHeaderHeight(){
  document.documentElement.style.setProperty('--header-h', header.offsetHeight + 'px');
}
syncHeaderHeight();
window.addEventListener('resize', syncHeaderHeight);

// Mobile nav toggle (menu déroulant : le header et la page restent fixes)
const burger = document.getElementById('burgerBtn');
const navOverlay = document.getElementById('navOverlay');

function lockScroll(){
  // Compense la largeur de la scrollbar pour que la page ne "bouge" pas
  const scrollBarWidth = window.innerWidth - document.documentElement.clientWidth;
  document.body.style.paddingRight = scrollBarWidth + 'px';
  document.body.style.overflow = 'hidden';
}
function unlockScroll(){
  document.body.style.paddingRight = '';
  document.body.style.overflow = '';
}
function openMobileNav(){
  syncHeaderHeight();
  document.body.classList.add('nav-open');
  burger.setAttribute('aria-expanded', 'true');
  lockScroll();
}
function closeMobileNav(){
  document.body.classList.remove('nav-open');
  burger.setAttribute('aria-expanded', 'false');
  unlockScroll();
}
burger.addEventListener('click', () => {
  document.body.classList.contains('nav-open') ? closeMobileNav() : openMobileNav();
});
navOverlay.addEventListener('click', closeMobileNav);
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && document.body.classList.contains('nav-open')) closeMobileNav();
});

// ============ SEARCH MODAL ============
// Ouvert depuis le bouton loupe (nav desktop) ou l'entrée "Rechercher" du
// panneau mobile — un seul modal partagé, ciblé par la classe .js-open-search.
const searchOverlay = document.getElementById('searchModalOverlay');
const searchInput = document.getElementById('searchModalInput');

function openSearchModal(){
  closeMobileNav();
  searchOverlay.classList.add('open');
  lockScroll();
  setTimeout(() => searchInput.focus(), 50);
}
function closeSearchModal(){
  searchOverlay.classList.remove('open');
  unlockScroll();
}
document.querySelectorAll('.js-open-search').forEach(btn => {
  btn.addEventListener('click', openSearchModal);
});
document.getElementById('closeSearchBtn').addEventListener('click', closeSearchModal);
searchOverlay.addEventListener('click', (e) => {
  if (e.target === searchOverlay) closeSearchModal();
});
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && searchOverlay.classList.contains('open')) closeSearchModal();
});

// Close menu on link click; if the link targets a role space, switch to that role tab first
document.querySelectorAll('.mobile-panel a').forEach(el => {
  el.addEventListener('click', () => {
    const roleKey = el.dataset.roleLink;
    if (roleKey) {
      const targetTab = document.querySelector('.role-tab[data-role="' + roleKey + '"]');
      if (targetTab) targetTab.click();
    }
    closeMobileNav();
  });
});

// Animated counters — les valeurs cibles viennent des attributs data-target,
// injectés par le contrôleur (HomeController) à partir des données réelles en
// base, et non de chiffres de démonstration figés.
const counters = Array.from(document.querySelectorAll('.hero-stat b[data-target]')).map(el => ({
  el,
  target: parseInt(el.dataset.target || '0', 10),
  suffix: el.dataset.suffix || '',
}));
let countersStarted = false;
function animateCounters(){
  if (countersStarted) return;
  countersStarted = true;
  counters.forEach(c => {
    let start = 0;
    const duration = 1400;
    const startTime = performance.now();
    function tick(now){
      const progress = Math.min((now - startTime) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const value = Math.floor(eased * c.target);
      c.el.textContent = value.toLocaleString('fr-FR') + c.suffix;
      if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  });
}
const heroObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => { if (entry.isIntersecting) animateCounters(); });
}, { threshold: 0.3 });
heroObserver.observe(document.querySelector('.hero-stats'));

// Listings carousel controls
const track = document.getElementById('listingsTrack');
document.getElementById('scrollLeft').addEventListener('click', () => {
  track.scrollBy({ left: -300, behavior: 'smooth' });
});
document.getElementById('scrollRight').addEventListener('click', () => {
  track.scrollBy({ left: 300, behavior: 'smooth' });
});

// Role tabs
const roleTabs = document.querySelectorAll('.role-tab');
roleTabs.forEach(tab => {
  tab.addEventListener('click', () => {
    roleTabs.forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    document.querySelectorAll('.roles-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-' + tab.dataset.role).classList.add('active');
  });
});

// (Ancien widget de recherche du hero retiré — remplacé par la recherche
// générale accessible depuis la barre de navigation, voir SearchController.)

// ============ SCROLL REVEAL ============
// Ajoute un joli effet d'apparition (fondu + glissement) à chaque section
// et à chaque carte, avec un léger effet de cascade, au fur et à mesure
// qu'elles entrent dans la fenêtre visible.
(function(){
  if (!('IntersectionObserver' in window)) return;

  const revealables = [];

  function tag(selector, className, staggerStep){
    document.querySelectorAll(selector).forEach((el, i) => {
      el.classList.add(className);
      if (staggerStep) el.style.setProperty('--reveal-delay', (i * staggerStep) + 's');
      revealables.push(el);
    });
  }

  // Titre + intro de chaque section
  tag('.section-head', 'reveal-up');

  // Grilles de cartes : effet de cascade (chaque carte un peu après la précédente)
  tag('.species-card', 'reveal-card', 0.08);
  tag('.step-card', 'reveal-card', 0.1);
  tag('.listing-card', 'reveal-card', 0.07);
  tag('.price-card', 'reveal-card', 0.12);
  tag('.testi-card', 'reveal-card', 0.1);
  tag('.news-card', 'reveal-card', 0.1);
  tag('.trace-point', 'reveal-up', 0.12);

  // Blocs particuliers
  tag('.roles-tabs', 'reveal-up');
  tag('.role-visual', 'reveal-left');
  tag('.trace-visual', 'reveal-zoom');
  tag('.cta-final', 'reveal-up');

  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

  revealables.forEach(el => revealObserver.observe(el));
})();

// Footer year
document.getElementById('year').textContent = new Date().getFullYear();
