<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Mon espace — ElevConnect')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;0,9..144,900;1,9..144,600&family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/mon-espace.css') }}">
@stack('styles')
</head>
<body>

@php
  $r = fn (string $name) => request()->routeIs($name);
  $role = auth()->user()->role;
  $estFournisseur = auth()->user()->estFournisseur();
@endphp

<div class="me-shell">

  <div class="me-overlay" id="meOverlay"></div>

  <aside class="me-sidebar" id="meSidebar">
    <a href="{{ route('home') }}" class="me-sidebar-brand">
      <svg viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="24" fill="#D79B2A"/><path d="M14 30c0-7 4.5-12 10-12s10 5 10 12" stroke="#1E3626" stroke-width="2.4" fill="none" stroke-linecap="round"/><circle cx="24" cy="16" r="3.4" fill="#1E3626"/></svg>
      ElevConnect
    </a>
    <div class="me-sidebar-user">
      <b>{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</b>
      {{ ['eleveur'=>'Éleveur','acheteur'=>'Acheteur','vendeur_provende'=>'Vendeur de provende','vendeur_accessoire'=>"Vendeur d'accessoires",'veterinaire'=>'Vétérinaire','livreur'=>'Livreur','administrateur'=>'Administrateur'][$role] ?? $role }}
    </div>

    <nav>
      <div class="me-nav-group">
        <a href="{{ route('mon-espace.dashboard') }}" class="me-nav-link {{ $r('mon-espace.dashboard') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg>
          Tableau de bord
        </a>
      </div>

      @if ($estFournisseur)
        <div class="me-nav-group">
          <div class="me-nav-title">Mes ventes</div>
          <a href="{{ route('mon-espace.annonces.index') }}" class="me-nav-link {{ $r('mon-espace.annonces.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M4 15c-1.5-1-2-3 0-4 .3-2 2-3 4-2.6C9 6.7 11 6 12 7c1-1 3-.3 4 1.4 2-.4 3.7.6 4 2.6 2 1 1.5 3 0 4-.3 2.6-2.5 4-5 4H9c-2.5 0-4.7-1.4-5-4z"/></svg>
            Mes annonces
          </a>
          <a href="{{ route('mon-espace.commandes-recues.index') }}" class="me-nav-link {{ $r('mon-espace.commandes-recues.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V5a4 4 0 018 0v2"/></svg>
            Commandes reçues
          </a>
        </div>
      @endif

      @if ($role === 'livreur')
        <div class="me-nav-group">
          <div class="me-nav-title">Livraisons</div>
          <a href="{{ route('mon-espace.livraisons.proposees') }}" class="me-nav-link {{ $r('mon-espace.livraisons.proposees') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><rect x="3" y="9" width="12" height="8" rx="1.5"/><path d="M15 12h3l3 3v2h-6z"/></svg>
            Proposées
          </a>
          <a href="{{ route('mon-espace.livraisons.mes') }}" class="me-nav-link {{ $r('mon-espace.livraisons.mes') || $r('mon-espace.livraisons.show') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            Mes livraisons
          </a>
        </div>
      @endif

      @if ($role === 'veterinaire')
        <div class="me-nav-group">
          <div class="me-nav-title">Cabinet</div>
          <a href="{{ route('mon-espace.services.index') }}" class="me-nav-link {{ $r('mon-espace.services.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M4 16c0-5 3-9 8-9s8 4 8 9"/><circle cx="12" cy="18" r="2"/></svg>
            Mes services
          </a>
          <a href="{{ route('mon-espace.rendez-vous-recus.index') }}" class="me-nav-link {{ $r('mon-espace.rendez-vous-recus.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
            Rendez-vous reçus
          </a>
          <a href="{{ route('mon-espace.abonnement.show') }}" class="me-nav-link {{ $r('mon-espace.abonnement.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M12 2l3 7h7l-5.5 4.3L18.5 21 12 16.5 5.5 21l2-7.7L2 9h7z"/></svg>
            Abonnement
          </a>
        </div>
      @endif

      @if ($role === 'eleveur')
        <div class="me-nav-group">
          <div class="me-nav-title">Santé animale</div>
          <a href="{{ route('mon-espace.rendez-vous.index') }}" class="me-nav-link {{ $r('mon-espace.rendez-vous.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
            Mes rendez-vous
          </a>
        </div>
      @endif

      <div class="me-nav-group">
        <div class="me-nav-title">Achats</div>
        <a href="{{ route('mon-espace.commandes.index') }}" class="me-nav-link {{ $r('mon-espace.commandes.*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V5a4 4 0 018 0v2"/></svg>
          Mes commandes
        </a>
      </div>

      @if (auth()->user()->peutPublierActualite())
        <div class="me-nav-group">
          <div class="me-nav-title">Contenu</div>
          <a href="{{ route('mon-espace.actualites.create') }}" class="me-nav-link {{ $r('mon-espace.actualites.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M4 4h16v12H8l-4 4z"/></svg>
            Publier une actualité
          </a>
        </div>
      @endif

      @if ($role === 'administrateur')
        <div class="me-nav-group">
          <div class="me-nav-title">Administration</div>
          <a href="{{ route('admin.dashboard') }}" class="me-nav-link {{ $r('admin.dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg>
            Vue d'ensemble
          </a>
          <a href="{{ route('admin.moderation.index') }}" class="me-nav-link {{ $r('admin.moderation.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Modération
          </a>
          <a href="{{ route('admin.litiges.index') }}" class="me-nav-link {{ $r('admin.litiges.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L2.5 18a1.5 1.5 0 001.3 2.2h16.4a1.5 1.5 0 001.3-2.2L13.7 3.9a1.5 1.5 0 00-2.6 0z"/></svg>
            Litiges
          </a>
          <a href="{{ route('admin.utilisateurs.index') }}" class="me-nav-link {{ $r('admin.utilisateurs.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-4 3-6 7-6s7 2 7 6"/><circle cx="17" cy="8" r="2.5"/><path d="M22 20c0-3-2-5-4.5-5.5"/></svg>
            Utilisateurs
          </a>
        </div>
      @endif

      <div class="me-nav-group">
        <div class="me-nav-title">Compte</div>
        <a href="{{ route('mon-espace.notifications.index') }}" class="me-nav-link {{ $r('mon-espace.notifications.*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
          Notifications
        </a>
        <a href="{{ route('mon-espace.profil.edit') }}" class="me-nav-link {{ $r('mon-espace.profil.*') ? 'active' : '' }}">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
          Mon profil
        </a>
        <a href="{{ route('catalogue.index') }}" class="me-nav-link">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
          Retour au site
        </a>
      </div>
    </nav>

    <div class="me-sidebar-footer">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Déconnexion</button>
      </form>
    </div>
  </aside>

  <div style="flex:1;min-width:0;display:flex;flex-direction:column;">
    <div class="me-topbar">
      <a href="{{ route('home') }}" class="me-topbar-brand">
        <svg viewBox="0 0 48 48" fill="none"><circle cx="24" cy="24" r="24" fill="#D79B2A"/><path d="M14 30c0-7 4.5-12 10-12s10 5 10 12" stroke="#1E3626" stroke-width="2.4" fill="none" stroke-linecap="round"/></svg>
        ElevConnect
      </a>
      <button type="button" class="me-burger" id="meBurgerBtn" aria-label="Ouvrir le menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round"/></svg>
      </button>
    </div>

    <main class="me-content">
      @if (session('status'))
        <div class="dash-card" style="background:var(--pasture-soft);border:none;color:var(--pasture-dark);font-weight:600;">
          {{ session('status') }}
        </div>
      @endif

      @yield('content')
    </main>
  </div>
</div>

<script>
  const meBurgerBtn = document.getElementById('meBurgerBtn');
  const meSidebar = document.getElementById('meSidebar');
  const meOverlay = document.getElementById('meOverlay');
  function meToggle(){ meSidebar.classList.toggle('open'); meOverlay.classList.toggle('open'); }
  meBurgerBtn?.addEventListener('click', meToggle);
  meOverlay?.addEventListener('click', meToggle);
</script>
@stack('scripts')
</body>
</html>
