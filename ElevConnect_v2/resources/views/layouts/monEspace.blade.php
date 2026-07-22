<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Mon espace — ElevConnect')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;0,9..144,900;1,9..144,600&family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
{{-- Même design system / animation de chargement progressif que le layout
     public "app" — voir resources/css/app.css, resources/js/app.js et
     vite.config.js. dashboard.css reste un fichier séparé (spécifique à ce
     layout), chargé ici directement plutôt que via Vite. --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
@stack('styles')
</head>
<body class="mes-body">

@php
  $u = auth()->user();

  // Correspondance route active -> libellé affiché dans la petite entête
  // horizontale. Réutilisée aussi pour l'état actif de la barre latérale.
  $sections = [
    'mon-espace.dashboard'                      => 'Tableau de bord',
    'mon-espace.annonces.*'                     => 'Mon catalogue',
    'mon-espace.commandes-fournisseur.*'        => 'Commandes reçues',
    'mon-espace.livraison.proposees'            => 'Livraisons proposées',
    'mon-espace.livraison.*'                    => 'Mes livraisons',
    'mon-espace.planning.*'                     => 'Mon planning',
    'mon-espace.services.*'                     => 'Mes services',
    'mon-espace.rendez-vous-recus.*'            => 'Rendez-vous reçus',
    'mon-espace.abonnement.*'                   => 'Mon abonnement',
    'mon-espace.versements.*'                   => 'Mes versements',
    'mon-espace.rendez-vous.*'                  => 'Mes rendez-vous',
    'mon-espace.commandes.*'                    => 'Mes achats',
    'actualites.*'                              => 'Actualités',
    'mon-espace.actualites.*'                   => 'Actualités',
    'mon-espace.notifications.*'                => 'Notifications',
    'mon-espace.profile.*'                      => 'Mon profil',
    'mon-espace.admin.*'                        => 'Administration',
  ];
  $pageLabel = 'Mon espace';
  foreach ($sections as $pattern => $label) {
    if (request()->routeIs($pattern)) { $pageLabel = $label; break; }
  }

  // Fil d'ariane : Tableau de bord > Section > Action (le 3ᵉ niveau n'apparaît
  // que sur les pages create/edit/show, déduit du nom de la route active).
  $actionLabels = [
    'create' => 'Publier', 'store' => 'Publier',
    'edit' => 'Modifier', 'update' => 'Modifier',
    'show' => 'Détail',
  ];
  $routeName = request()->route()?->getName() ?? '';
  $routeAction = substr($routeName, strrpos($routeName, '.') + 1);
  $actionLabel = $actionLabels[$routeAction] ?? null;
  if (request()->routeIs('mon-espace.dashboard')) {
    $actionLabel = null;
  }

  $roleLabels = [
    'eleveur' => 'Éleveur', 'acheteur' => 'Acheteur',
    'vendeur_provende' => 'Vendeur de provende', 'vendeur_accessoire' => "Vendeur d'accessoires",
    'veterinaire' => 'Vétérinaire', 'livreur' => 'Livreur', 'administrateur' => 'Administrateur',
  ];
  $roleLabel = $roleLabels[$u->role] ?? $u->role;
@endphp

<div class="mes-shell">

  <aside class="mes-sidebar">
    <a href="{{ route('home') }}" class="mes-logo">
      <svg viewBox="0 0 48 48" fill="none" width="30" height="30">
        <circle cx="24" cy="24" r="24" fill="#2F4F38"/>
        <path d="M14 30c0-7 4.5-12 10-12s10 5 10 12" stroke="#D79B2A" stroke-width="2.4" fill="none" stroke-linecap="round"/>
        <circle cx="24" cy="16" r="3.4" fill="#F3EBD8"/>
        <path d="M24 19.4v4" stroke="#F3EBD8" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <span class="mes-logo-text">
        ElevConnect
        <small>{{ $roleLabel }}</small>
      </span>
    </a>

    <nav class="mes-nav">
      <div class="mes-nav-group">
        <a href="{{ route('mon-espace.dashboard') }}" class="{{ request()->routeIs('mon-espace.dashboard') ? 'active' : '' }}"><i class="fa-solid fa-gauge"></i> Tableau de bord</a>
      </div>

      @if ($u->estFournisseur())
        <div class="mes-nav-group">
          <a href="{{ route('mon-espace.annonces.index') }}" class="{{ request()->routeIs('mon-espace.annonces.*') ? 'active' : '' }}"><i class="fa-solid fa-box"></i> Mon catalogue</a>
          <a href="{{ route('mon-espace.commandes-fournisseur.index') }}" class="{{ request()->routeIs('mon-espace.commandes-fournisseur.*') ? 'active' : '' }}"><i class="fa-solid fa-receipt"></i> Commandes reçues</a>
          <a href="{{ route('mon-espace.versements.index') }}" class="{{ request()->routeIs('mon-espace.versements.*') ? 'active' : '' }}"><i class="fa-solid fa-money-bill-wave"></i> Mes versements</a>
        </div>
      @endif

      @if ($u->role === 'livreur')
        <div class="mes-nav-group">
          <a href="{{ route('mon-espace.livraison.proposees') }}" class="{{ request()->routeIs('mon-espace.livraison.proposees') ? 'active' : '' }}"><i class="fa-solid fa-truck"></i> Livraisons proposées</a>
          <a href="{{ route('mon-espace.livraison.mes') }}" class="{{ request()->routeIs('mon-espace.livraison.mes') || request()->routeIs('mon-espace.livraison.show') ? 'active' : '' }}"><i class="fa-solid fa-route"></i> Mes livraisons</a>
          <a href="{{ route('mon-espace.planning.index') }}" class="{{ request()->routeIs('mon-espace.planning.*') ? 'active' : '' }}"><i class="fa-solid fa-calendar-days"></i> Mon planning</a>
          <a href="{{ route('mon-espace.versements.index') }}" class="{{ request()->routeIs('mon-espace.versements.*') ? 'active' : '' }}"><i class="fa-solid fa-money-bill-wave"></i> Mes versements</a>
        </div>
      @endif

      @if ($u->role === 'veterinaire')
        <div class="mes-nav-group">
          <a href="{{ route('mon-espace.services.index') }}" class="{{ request()->routeIs('mon-espace.services.*') ? 'active' : '' }}"><i class="fa-solid fa-stethoscope"></i> Mes services</a>
          <a href="{{ route('mon-espace.rendez-vous-recus.index') }}" class="{{ request()->routeIs('mon-espace.rendez-vous-recus.*') ? 'active' : '' }}"><i class="fa-solid fa-calendar-days"></i> Rendez-vous reçus</a>
          <a href="{{ route('mon-espace.abonnement.show') }}" class="{{ request()->routeIs('mon-espace.abonnement.*') ? 'active' : '' }}"><i class="fa-solid fa-star"></i> Mon abonnement</a>
        </div>
      @endif

      @if ($u->role === 'eleveur')
        <div class="mes-nav-group">
          <a href="{{ route('mon-espace.rendez-vous.index') }}" class="{{ request()->routeIs('mon-espace.rendez-vous.*') ? 'active' : '' }}"><i class="fa-solid fa-calendar-days"></i> Mes rendez-vous</a>
        </div>
      @endif

      <div class="mes-nav-group">
        <a href="{{ route('mon-espace.commandes.index') }}" class="{{ request()->routeIs('mon-espace.commandes.*') ? 'active' : '' }}"><i class="fa-solid fa-cart-shopping"></i> Mes achats</a>
        @if ($u->peutPublierActualite())
          <a href="{{ route('actualites.index') }}" class="{{ request()->routeIs('actualites.*') || request()->routeIs('mon-espace.actualites.*') ? 'active' : '' }}"><i class="fa-solid fa-newspaper"></i> Actualités</a>
        @endif
        <a href="{{ route('mon-espace.notifications.index') }}" class="{{ request()->routeIs('mon-espace.notifications.*') ? 'active' : '' }}"><i class="fa-solid fa-bell"></i> Notifications</a>
        <a href="{{ route('mon-espace.profile.edit') }}" class="{{ request()->routeIs('mon-espace.profile.*') ? 'active' : '' }}"><i class="fa-solid fa-user"></i> Mon profil</a>
      </div>

      @if ($u->role === 'administrateur')
        <div class="mes-nav-group">
          <a href="{{ route('mon-espace.admin.dashboard') }}" class="{{ request()->routeIs('mon-espace.admin.*') ? 'active' : '' }}"><i class="fa-solid fa-screwdriver-wrench"></i> Administration</a>
        </div>
      @endif
    </nav>

    <div class="mes-sidebar-foot">
      <a href="{{ route('mon-espace.profile.edit') }}" class="mes-user-card">
        <img src="{{ $u->photo_profil ? asset('storage/'.$u->photo_profil) : asset('images/avatar-placeholder.svg') }}" alt="">
        <span>
          <b>{{ $u->prenom }} {{ $u->nom }}</b>
          <small>{{ $roleLabel }}</small>
        </span>
      </a>
      <a href="{{ route('home') }}" class="mes-foot-link"><i class="fa-solid fa-arrow-up-right-from-square"></i> Voir le site public</a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="mes-foot-link mes-logout"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</button>
      </form>
    </div>
  </aside>

  <div class="mes-main">
    <header class="mes-topbar">
      <h2>{{ $pageLabel }}</h2>
      <div class="mes-topbar-actions">
        <a href="{{ route('mon-espace.notifications.index') }}" title="Notifications"><i class="fa-solid fa-bell"></i></a>
      </div>
    </header>

    <nav class="mes-breadcrumb" aria-label="Fil d'ariane">
      <a href="{{ route('mon-espace.dashboard') }}"><i class="fa-solid fa-house"></i></a>
      <i class="fa-solid fa-chevron-right"></i>
      @if ($actionLabel)
        <span class="is-muted">{{ $pageLabel }}</span>
        <i class="fa-solid fa-chevron-right"></i>
        <span>{{ $actionLabel }}</span>
      @else
        <span>{{ $pageLabel }}</span>
      @endif
    </nav>

    <main class="mes-content">
      @yield('content')
    </main>
  </div>

</div>

@stack('scripts')
</body>
</html>
