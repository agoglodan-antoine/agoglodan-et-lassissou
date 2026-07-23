<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Mon espace — ElevConnect')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;0,9..144,900;1,9..144,600&family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
@stack('styles')
</head>
<body class="mes-body">

@php
  $u = auth()->user();

  // Correspondance route active -> libellé + route "index" de la section,
  // affichés dans la petite entête horizontale et dans le fil d'ariane.
  // Réutilisée aussi pour l'état actif de la barre latérale. La route est
  // celle vers laquelle pointe le maillon "section" du fil d'ariane quand on
  // se trouve sur une page enfant (create/edit/show) de cette section.
  $sections = [
    'mon-espace.dashboard'                      => ['label' => 'Tableau de bord',         'route' => 'mon-espace.dashboard'],
    'mon-espace.annonces.*'                     => ['label' => 'Mon catalogue',            'route' => 'mon-espace.annonces.index'],
    'mon-espace.commandes-fournisseur.*'        => ['label' => 'Commandes',                'route' => 'mon-espace.commandes-fournisseur.index'],
    'mon-espace.livraison.proposees'            => ['label' => 'Livraisons proposées',     'route' => 'mon-espace.livraison.proposees'],
    'mon-espace.livraison.*'                    => ['label' => 'Historique des livraisons','route' => 'mon-espace.livraison.mes'],
    'mon-espace.planning.*'                     => ['label' => 'Mon planning',             'route' => 'mon-espace.planning.index'],
    'mon-espace.services.*'                     => ['label' => 'Mes services',             'route' => 'mon-espace.services.index'],
    'mon-espace.rendez-vous-recus.*'            => ['label' => 'Rendez-vous reçus',        'route' => 'mon-espace.rendez-vous-recus.index'],
    'mon-espace.abonnement.*'                   => ['label' => 'Mon abonnement',           'route' => 'mon-espace.abonnement.show'],
    'mon-espace.versements.*'                   => ['label' => 'Mes versements',           'route' => 'mon-espace.versements.index'],
    'mon-espace.rendez-vous.*'                  => ['label' => 'Mes rendez-vous',          'route' => 'mon-espace.rendez-vous.index'],
    'mon-espace.commandes.*'                    => ['label' => 'Mes achats',            'route' => 'mon-espace.commandes.index'],
    'actualites.*'                              => ['label' => 'Actualités',               'route' => 'actualites.index'],
    'mon-espace.actualites.*'                   => ['label' => 'Actualités',               'route' => 'actualites.index'],
    'mon-espace.notifications.*'                => ['label' => 'Notifications',            'route' => 'mon-espace.notifications.index'],
    'mon-espace.profile.*'                      => ['label' => 'Mon profil',               'route' => 'mon-espace.profile.edit'],
    'mon-espace.admin.*'                        => ['label' => 'Administration',           'route' => 'mon-espace.admin.dashboard'],
  ];
  $pageLabel = 'Mon espace';
  $sectionRoute = null;
  foreach ($sections as $pattern => $section) {
    if (request()->routeIs($pattern)) {
      $pageLabel = $section['label'];
      $sectionRoute = $section['route'];
      break;
    }
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

  <aside class="mes-sidebar" id="mesSidebar">
    <a href="{{ route('home') }}" class="mes-logo">
      <img src="{{ asset('favicon.png') }}" alt="ElevConnect" width="30" height="30">
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
      <a href="{{ route('home') }}" class="mes-foot-link"><i class="fa-solid fa-arrow-up-right-from-square"></i> <span>Voir le site public</span></a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="mes-foot-link mes-logout"><i class="fa-solid fa-right-from-bracket"></i> <span>Déconnexion</span></button>
      </form>
    </div>
  </aside>

  <div class="mes-sidebar-overlay" id="mesSidebarOverlay"></div>

  <div class="mes-main">
    <header class="mes-topbar">
      <button type="button" class="mes-topbar-burger" id="mesBurgerBtn" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mesSidebar">
        <span></span><span></span><span></span>
      </button>
      <h2>{{ $pageLabel }}</h2>
      <div class="mes-topbar-actions">
        <a href="{{ route('mon-espace.notifications.index') }}" title="Notifications"><i class="fa-solid fa-bell"></i></a>
      </div>
    </header>

    <nav class="mes-breadcrumb" aria-label="Fil d'ariane">
      <a href="{{ route('mon-espace.dashboard') }}"><i class="fa-solid fa-house"></i></a>
      <i class="fa-solid fa-chevron-right"></i>
      @if ($actionLabel)
        @if ($sectionRoute && $sectionRoute !== 'mon-espace.dashboard')
          <a href="{{ route($sectionRoute) }}" class="is-muted">{{ $pageLabel }}</a>
        @else
          <span class="is-muted">{{ $pageLabel }}</span>
        @endif
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
