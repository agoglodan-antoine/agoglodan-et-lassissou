<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'ElevConnect — Le bétail de votre région, à portée de main')</title>
<meta name="description" content="ElevConnect connecte éleveurs, vendeurs de provende, vendeurs d'accessoires, acheteurs, vétérinaires et livreurs partout au Bénin.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;0,9..144,900;1,9..144,600&family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@stack('styles')
</head>
<body>

<!-- ============ HEADER ============ -->
<header class="site-header" id="siteHeader">
  <div class="container nav-row">
    <a href="{{ route('home') }}#top" class="logo">
      <svg class="logo-mark" viewBox="0 0 48 48" fill="none">
        <circle cx="24" cy="24" r="24" fill="#2F4F38"/>
        <path d="M14 30c0-7 4.5-12 10-12s10 5 10 12" stroke="#D79B2A" stroke-width="2.4" fill="none" stroke-linecap="round"/>
        <circle cx="24" cy="16" r="3.4" fill="#F3EBD8"/>
        <path d="M24 19.4v4" stroke="#F3EBD8" stroke-width="2" stroke-linecap="round"/>
      </svg>
      ElevConnect
    </a>
    <nav class="nav-links">
      <a href="{{ route('catalogue.index') }}">Annonces</a>
      <a href="{{ route('veterinaires.index') }}">Vétérinaires</a>
      <a href="{{ route('actualites.index') }}">Actualités</a>
    </nav>
    <div class="nav-actions">
      <button type="button" class="btn btn-ghost-dark js-open-search hide-mobile" title="Rechercher" aria-haspopup="dialog">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M20 20l-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      </button>
      @auth
        <a href="{{ route('notifications.index') }}" class="btn btn-ghost-dark" title="Notifications">🔔</a>
        <a href="{{ route('dashboard') }}" class="btn btn-ghost-dark">Mon espace</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-primary">Déconnexion</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="btn btn-ghost-dark">Connexion</a>
        <a href="{{ route('register') }}" class="btn btn-primary">Créer un compte</a>
      @endauth
    </div>
    <button class="burger" id="burgerBtn" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mobilePanel">
      <span></span><span></span><span></span>
    </button>
  </div>

  <div class="nav-overlay" id="navOverlay"></div>

  <nav class="mobile-panel" id="mobilePanel" aria-label="Menu mobile">
   <div class="mp-collapse">
    <div class="mp-body">
      <div class="mp-group">
        <div class="mp-group-title">Découvrir</div>
        <button type="button" class="mp-link js-open-search" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;">
          <span class="mp-icon"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg></span>
          Rechercher
        </button>
        <a href="{{ route('catalogue.index') }}" class="mp-link">
          <span class="mp-icon"><svg viewBox="0 0 24 24"><path d="M4 15c-1.5-1-2-3 0-4 .3-2 2-3 4-2.6C9 6.7 11 6 12 7c1-1 3-.3 4 1.4 2-.4 3.7.6 4 2.6 2 1 1.5 3 0 4-.3 2.6-2.5 4-5 4H9c-2.5 0-4.7-1.4-5-4z"/></svg></span>
          Annonces
        </a>
        <a href="{{ route('home') }}#especes" class="mp-link">
          <span class="mp-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="13" r="7"/><circle cx="7" cy="8" r="2"/><circle cx="17" cy="8" r="2"/></svg></span>
          Espèces &amp; races
        </a>
        <a href="{{ route('veterinaires.index') }}" class="mp-link">
          <span class="mp-icon"><svg viewBox="0 0 24 24"><path d="M4 16c0-5 3-9 8-9s8 4 8 9"/><circle cx="12" cy="18" r="2"/></svg></span>
          Vétérinaires
        </a>
        <a href="{{ route('home') }}#abonnements" class="mp-link">
          <span class="mp-icon"><svg viewBox="0 0 24 24"><path d="M12 2l3 7h7l-5.5 4.3L18.5 21 12 16.5 5.5 21l2-7.7L2 9h7z"/></svg></span>
          Abonnements
        </a>
        <a href="{{ route('actualites.index') }}" class="mp-link">
          <span class="mp-icon"><svg viewBox="0 0 24 24"><path d="M4 4h16v12H8l-4 4z"/></svg></span>
          Actualités
        </a>
      </div>

      <div class="mp-divider"></div>

      <div class="mp-actions">
        @auth
          <a href="{{ route('notifications.index') }}" class="btn btn-ghost-dark">🔔 Notifications</a>
          <a href="{{ route('dashboard') }}" class="btn btn-ghost-dark">Mon espace</a>
        @else
          <a href="{{ route('login') }}" class="btn btn-ghost-dark">Connexion</a>
          <a href="{{ route('register') }}" class="btn btn-primary">Créer un compte</a>
        @endauth
      </div>

      <div class="mp-footer">
        <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 00-8.5 15.3L2 22l4.8-1.5A10 10 0 1012 2z"/></svg>
        Besoin d'aide ? Contactez-nous sur WhatsApp
      </div>
    </div>
   </div>
  </nav>
</header>

@if (session('status'))
  <div class="container" style="padding-top:14px;">
    <div style="background:var(--pasture-soft);color:var(--pasture-dark);padding:12px 18px;border-radius:var(--radius-sm);font-weight:600;">
      {{ session('status') }}
    </div>
  </div>
@endif

@yield('content')

<!-- ============ FOOTER ============ -->
<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-about">
      <div class="footer-logo">
        <svg viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="24" fill="#2F4F38"/><path d="M14 30c0-7 4.5-12 10-12s10 5 10 12" stroke="#D79B2A" stroke-width="2.4" fill="none" stroke-linecap="round"/><circle cx="24" cy="16" r="3.4" fill="#F3EBD8"/></svg>
        ElevConnect
      </div>
      <p>La plateforme qui structure la filière élevage béninoise : éleveurs, vendeurs de provende, vendeurs d'accessoires, acheteurs, vétérinaires et livreurs, partout dans le pays.</p>
      <div class="footer-social">
        <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M13 22v-8h3l.5-3.5H13V8.5c0-1 .3-1.7 1.8-1.7H17V3.3C16.6 3.3 15.4 3 14 3c-2.8 0-4.7 1.7-4.7 4.9V10.5H6V14h3.3v8z"/></svg></a>
        <a href="#" aria-label="WhatsApp"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 00-8.5 15.3L2 22l4.8-1.5A10 10 0 1012 2z"/></svg></a>
        <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4" fill="#152A1D"/><circle cx="17.3" cy="6.7" r="1.1" fill="#152A1D"/></svg></a>
      </div>
    </div>
    <div class="footer-col">
      <h5>Plateforme</h5>
      <ul>
        <li><a href="{{ route('catalogue.index') }}">Annonces</a></li>
        <li><a href="{{ route('home') }}#especes">Espèces &amp; races</a></li>
        <li><a href="{{ route('home') }}#abonnements">Abonnements vétérinaires</a></li>
        <li><a href="{{ route('actualites.index') }}">Actualités</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5>Acteurs</h5>
      <ul>
        <li><a href="{{ route('register') }}?role=eleveur">Éleveurs</a></li>
        <li><a href="{{ route('register') }}?role=acheteur">Acheteurs</a></li>
        <li><a href="{{ route('register') }}?role=veterinaire">Vétérinaires</a></li>
        <li><a href="{{ route('register') }}?role=livreur">Livreurs</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5>Contact</h5>
      <ul>
        <li>Parakou, Bénin</li>
        <li>contact@elevconnect.bj</li>
        <li>+229 01 00 00 00 00</li>
        <li>WhatsApp Business</li>
      </ul>
    </div>
  </div>
  <div class="container footer-bottom">
    <span>© <span id="year"></span> ElevConnect — Tous droits réservés.</span>
    <span>Institut Universitaire de Technologie de Parakou — Mémoire de licence professionnelle</span>
  </div>
</footer>

<!-- ============ SEARCH MODAL ============ -->
<div class="search-modal-overlay" id="searchModalOverlay" role="dialog" aria-modal="true" aria-label="Recherche générale">
  <div class="search-modal">
    <button type="button" class="search-modal-close" id="closeSearchBtn" aria-label="Fermer">✕</button>
    <h3>Rechercher sur ElevConnect</h3>
    <p class="form-hint">Annonces, actualités, services vétérinaires, services de transport.</p>
    <form action="{{ route('recherche.index') }}" method="GET" id="searchModalForm">
      <input type="text" name="q" id="searchModalInput" placeholder="Que recherchez-vous ?" autocomplete="off" required>
      <button type="submit" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="white" stroke-width="2"/><path d="M20 20l-4-4" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
        Rechercher
      </button>
    </form>
  </div>
</div>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
