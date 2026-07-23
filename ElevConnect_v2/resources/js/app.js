//resources/js/app.js
/* ===========================================================================
   ElevConnect — interactions transverses (header public, menu mobile,
   recherche, révélations au scroll) + widgets spécifiques à la page
   d'accueil (compteurs animés, onglets 'acteurs', carrousel d'annonces).
   Chaque bloc vérifie la présence de ses éléments avant de s'activer, car ce
   même fichier est chargé à la fois sur le layout public (app) et sur le
   layout applicatif (monEspace), qui n'ont pas tous les mêmes éléments.

   Point d'entrée Vite (voir vite.config.js) : compilé et injecté dans les
   deux layouts via @vite(['resources/css/app.css', 'resources/js/app.js']).
   Aucun import de bibliothèque externe n'est nécessaire ici (JavaScript
   natif uniquement) ; si un paquet npm est ajouté un jour (Alpine.js,
   par ex.), l'importer en haut de ce fichier suffit — Vite s'occupe du reste.
   =========================================================================== */

// ============ HEADER PUBLIC (absent du layout monEspace) ============
const header = document.getElementById('siteHeader');
if (header) {
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
    if (burger) burger.setAttribute('aria-expanded', 'true');
    lockScroll();
  }
  function closeMobileNav(){
    document.body.classList.remove('nav-open');
    if (burger) burger.setAttribute('aria-expanded', 'false');
    unlockScroll();
  }
  if (burger) {
    burger.addEventListener('click', () => {
      document.body.classList.contains('nav-open') ? closeMobileNav() : openMobileNav();
    });
  }
  if (navOverlay) navOverlay.addEventListener('click', closeMobileNav);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && document.body.classList.contains('nav-open')) closeMobileNav();
  });

  // ============ SEARCH MODAL ============
  // Ouvert depuis le bouton loupe (nav desktop) ou l'entrée "Rechercher" du
  // panneau mobile — un seul modal partagé, ciblé par la classe .js-open-search.
  const searchOverlay = document.getElementById('searchModalOverlay');
  const searchInput = document.getElementById('searchModalInput');

  if (searchOverlay) {
    function openSearchModal(){
      closeMobileNav();
      searchOverlay.classList.add('open');
      lockScroll();
      if (searchInput) setTimeout(() => searchInput.focus(), 50);
    }
    function closeSearchModal(){
      searchOverlay.classList.remove('open');
      unlockScroll();
    }
    document.querySelectorAll('.js-open-search').forEach(btn => {
      btn.addEventListener('click', openSearchModal);
    });
    const closeSearchBtn = document.getElementById('closeSearchBtn');
    if (closeSearchBtn) closeSearchBtn.addEventListener('click', closeSearchModal);
    searchOverlay.addEventListener('click', (e) => {
      if (e.target === searchOverlay) closeSearchModal();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && searchOverlay.classList.contains('open')) closeSearchModal();
    });
  }

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
}

// ============ PAGE D'ACCUEIL : compteurs animés ============
// Les valeurs cibles viennent des attributs data-target, injectés par le
// contrôleur (HomeController) à partir des données réelles en base.
const heroStats = document.querySelector('.hero-stats');
if (heroStats) {
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
  heroObserver.observe(heroStats);
}

// ============ PAGE D'ACCUEIL : carrousel d'annonces récentes ============
const listingsTrack = document.getElementById('listingsTrack');
if (listingsTrack) {
  const scrollLeftBtn = document.getElementById('scrollLeft');
  const scrollRightBtn = document.getElementById('scrollRight');
  if (scrollLeftBtn) scrollLeftBtn.addEventListener('click', () => {
    listingsTrack.scrollBy({ left: -300, behavior: 'smooth' });
  });
  if (scrollRightBtn) scrollRightBtn.addEventListener('click', () => {
    listingsTrack.scrollBy({ left: 300, behavior: 'smooth' });
  });
}

// ============ PAGE D'ACCUEIL : onglets 'acteurs' ============
const roleTabs = document.querySelectorAll('.role-tab');
if (roleTabs.length) {
  roleTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      roleTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      document.querySelectorAll('.roles-panel').forEach(p => p.classList.remove('active'));
      const panel = document.getElementById('panel-' + tab.dataset.role);
      if (panel) panel.classList.add('active');
    });
  });
}

// ============ CHARGEMENT PROGRESSIF (RÉVÉLATION AU SCROLL) ============
// Ajoute un effet d'apparition (fondu + glissement) à chaque section et à
// chaque carte, avec un léger effet de cascade, au fur et à mesure qu'elles
// entrent dans la fenêtre visible. Appliqué à la fois aux blocs spécifiques
// de la page d'accueil et aux blocs génériques utilisés dans tout le reste
// du site (cartes de tableau de bord, tableaux, formulaires...), pour que
// l'effet soit cohérent sur l'ensemble des pages, pas seulement l'accueil.
//
// ---------------------------------------------------------------------------
// RÉGLAGES — à modifier librement si l'effet paraît trop brusque ou trop lent.
// Le délai de base commun à tous les éléments (--reveal-base-delay) et la
// durée de l'animation (--reveal-duration) se règlent, eux, côté CSS —
// voir resources/css/app.css, bloc "RÉGLAGES DU CHARGEMENT PROGRESSIF".
// ---------------------------------------------------------------------------
//
// REVEAL_STAGGER_MULTIPLIER : multiplie l'écart entre deux éléments d'une
//   même série (ex. les cartes du catalogue qui apparaissent l'une après
//   l'autre). 1 = valeurs d'origine indiquées dans les appels tag() plus
//   bas ; augmenter (ex. 1.4, valeur actuelle) pour un effet de cascade plus
//   posé ; diminuer pour un effet plus rapide ; 0 pour supprimer la cascade
//   (tous les éléments d'une série apparaissent alors ensemble).
//
// REVEAL_MAX_STAGGER_ITEMS : au-delà de ce nombre d'éléments dans une même
//   série, le décalage cesse d'augmenter — pour qu'une longue liste ne mette
//   pas plusieurs secondes à finir d'apparaître.
//
// REVEAL_THRESHOLD : portion minimale (0 à 1) d'un élément qui doit être
//   visible à l'écran pour que l'IntersectionObserver le déclenche.
//
// REVEAL_ROOT_MARGIN : marge appliquée à la zone de détection. Une valeur
//   négative en bas (ex. "-60px") retarde légèrement le déclenchement, pour
//   qu'un élément n'apparaisse pas dès qu'il touche le tout bord de l'écran.
(function(){
  if (!('IntersectionObserver' in window)) return;

  const REVEAL_STAGGER_MULTIPLIER = 1.4;
  const REVEAL_MAX_STAGGER_ITEMS = 8;
  const REVEAL_THRESHOLD = 0.12;
  const REVEAL_ROOT_MARGIN = '0px 0px -60px 0px';

  const revealables = [];

  function tag(selector, className, staggerStep){
    document.querySelectorAll(selector).forEach((el, i) => {
      if (el.classList.contains('reveal-up') || el.classList.contains('reveal-card')
          || el.classList.contains('reveal-left') || el.classList.contains('reveal-zoom')) {
        return; // déjà tagué par une règle précédente, on ne le compte pas deux fois
      }
      el.classList.add(className);
      if (staggerStep) {
        const delai = Math.min(i, REVEAL_MAX_STAGGER_ITEMS) * staggerStep * REVEAL_STAGGER_MULTIPLIER;
        el.style.setProperty('--reveal-delay', delai + 's');
      }
      revealables.push(el);
    });
  }

  // --- Page d'accueil ---
  tag('.section-head', 'reveal-up');
  tag('.species-card', 'reveal-card', 0.08);
  tag('.step-card', 'reveal-card', 0.1);
  tag('.listing-card', 'reveal-card', 0.07);
  tag('.price-card', 'reveal-card', 0.12);
  tag('.testi-card', 'reveal-card', 0.1);
  tag('.news-card', 'reveal-card', 0.1);
  tag('.trace-point', 'reveal-up', 0.12);
  tag('.roles-tabs', 'reveal-up');
  tag('.role-visual', 'reveal-left');
  tag('.trace-visual', 'reveal-zoom');
  tag('.cta-final', 'reveal-up');

  // --- Reste du site (catalogue, mon espace, administration...) ---
  tag('.dash-head', 'reveal-up');
  tag('.auth-card', 'reveal-up');
  tag('.dash-card', 'reveal-card', 0.06);
  tag('.catalogue-card', 'reveal-card', 0.06);
  tag('.moderation-card', 'reveal-card', 0.06);
  tag('.stat-strip .stat-chip', 'reveal-card', 0.05);
  tag('.shortcut-grid .shortcut-card', 'reveal-card', 0.05);

  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: REVEAL_THRESHOLD, rootMargin: REVEAL_ROOT_MARGIN });

  revealables.forEach(el => revealObserver.observe(el));
})();

// ============ VALIDATION CLIENT DES CHAMPS FICHIER (images) ============
// S'applique à tout <input type="file" data-max-mb="4"> du site (annonces,
// services, actualités, photo de profil...) : vérifie taille et type dès la
// sélection, avec un message immédiat et précis, avant même la soumission —
// pour éviter les échecs d'envoi "sans précision du problème".
document.querySelectorAll('input[type="file"][data-max-mb]').forEach(input => {
  const maxMb = parseFloat(input.dataset.maxMb || '4');
  let hint = input.parentElement.querySelector('.file-check-hint');
  if (!hint) {
    hint = document.createElement('span');
    hint.className = 'form-hint file-check-hint';
    input.insertAdjacentElement('afterend', hint);
  }

  input.addEventListener('change', () => {
    hint.textContent = '';
    hint.style.color = '';
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const sizeMb = file.size / (1024 * 1024);

    if (!file.type.startsWith('image/')) {
      hint.textContent = 'Ce fichier n\u2019est pas une image reconnue par le navigateur.';
      hint.style.color = 'var(--clay-dark)';
      input.value = '';
      return;
    }

    if (sizeMb > maxMb) {
      hint.textContent = 'Fichier trop volumineux (' + sizeMb.toFixed(1) + ' Mo) — ' + maxMb + ' Mo maximum.';
      hint.style.color = 'var(--clay-dark)';
      input.value = '';
      return;
    }

    hint.textContent = file.name + ' (' + sizeMb.toFixed(1) + ' Mo) prêt à être envoyé.';
    hint.style.color = 'var(--pasture-dark)';
  });
});

// ============ MON ESPACE : tiroir latéral mobile ============
// Absent du layout public (app) : la sidebar, le bouton burger de la topbar
// et le voile d'arrière-plan n'existent que dans monEspace.blade.php.
// Sur grand écran, la sidebar reste toujours visible (voir app.css) ; ce
// bloc ne fait qu'ajouter/retirer la classe .open sur mobile.
const mesSidebar = document.getElementById('mesSidebar');
if (mesSidebar) {
  const mesBurgerBtn = document.getElementById('mesBurgerBtn');
  const mesOverlay = document.getElementById('mesSidebarOverlay');

  function openMesSidebar(){
    mesSidebar.classList.add('open');
    if (mesOverlay) mesOverlay.classList.add('open');
    if (mesBurgerBtn) mesBurgerBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }
  function closeMesSidebar(){
    mesSidebar.classList.remove('open');
    if (mesOverlay) mesOverlay.classList.remove('open');
    if (mesBurgerBtn) mesBurgerBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  if (mesBurgerBtn) {
    mesBurgerBtn.addEventListener('click', () => {
      mesSidebar.classList.contains('open') ? closeMesSidebar() : openMesSidebar();
    });
  }
  if (mesOverlay) mesOverlay.addEventListener('click', closeMesSidebar);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && mesSidebar.classList.contains('open')) closeMesSidebar();
  });
  // Referme le tiroir dès qu'on choisit un lien du menu (mobile uniquement,
  // mais inoffensif sur grand écran puisque le tiroir n'y est jamais "open").
  mesSidebar.querySelectorAll('a, button').forEach(el => {
    el.addEventListener('click', closeMesSidebar);
  });
}

// Footer year (absent du layout monEspace, qui n'a pas de pied de page)
const yearEl = document.getElementById('year');
if (yearEl) yearEl.textContent = new Date().getFullYear();
