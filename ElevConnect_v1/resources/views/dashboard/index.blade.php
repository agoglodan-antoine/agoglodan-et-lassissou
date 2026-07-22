@extends('layouts.mon-espace')

@section('title', 'Mon tableau de bord — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@php
  $roleLabels = [
    'eleveur' => 'Éleveur', 'acheteur' => 'Acheteur',
    'vendeur_provende' => 'Vendeur de provende', 'vendeur_accessoire' => "Vendeur d'accessoires",
    'veterinaire' => 'Vétérinaire', 'livreur' => 'Livreur', 'administrateur' => 'Administrateur',
  ];
@endphp

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Bonjour {{ $user->prenom }} 👋</h1>
        <p>{{ $roleLabels[$user->role] ?? $user->role }} — voici un aperçu de votre activité.</p>
      </div>
      @if ($stats['notifications_non_lues'] > 0)
        <a href="{{ route('mon-espace.notifications.index') }}" class="btn btn-ghost-dark">🔔 {{ $stats['notifications_non_lues'] }} nouvelle(s) notification(s)</a>
      @endif
    </div>

    {{-- Indicateurs --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:18px;margin-bottom:36px;">
      @if ($user->estFournisseur())
        <div class="stat-tile"><span class="form-hint">Mes annonces</span><b>{{ $stats['annonces_total'] }}</b></div>
        <div class="stat-tile"><span class="form-hint">Annonces visibles</span><b>{{ $stats['annonces_visibles'] }}</b></div>
        <div class="stat-tile"><span class="form-hint">En attente de modération</span><b>{{ $stats['annonces_en_attente'] }}</b></div>
        <div class="stat-tile"><span class="form-hint">Commandes à traiter</span><b>{{ $stats['commandes_a_traiter'] }}</b></div>
        <div class="stat-tile"><span class="form-hint">Ventes confirmées</span><b>{{ $stats['commandes_confirmees'] }}</b></div>
        <div class="stat-tile"><span class="form-hint">Chiffre d'affaires net</span><b>{{ number_format($stats['chiffre_affaires'], 0, ',', ' ') }} <small style="font-size:0.9rem;">FCFA</small></b></div>
      @endif

      @if ($user->role === 'livreur')
        <div class="stat-tile"><span class="form-hint">Livraisons proposées</span><b>{{ $stats['livraisons_proposees'] }}</b></div>
        <div class="stat-tile"><span class="form-hint">Mes livraisons en cours</span><b>{{ $stats['mes_livraisons_en_cours'] }}</b></div>
        <div class="stat-tile"><span class="form-hint">Livraisons terminées</span><b>{{ $stats['mes_livraisons_terminees'] }}</b></div>
        <div class="stat-tile"><span class="form-hint">Revenus de livraison</span><b>{{ number_format($stats['revenus_livraison'], 0, ',', ' ') }} <small style="font-size:0.9rem;">FCFA</small></b></div>
      @endif

      @if ($user->role === 'veterinaire')
        <div class="stat-tile"><span class="form-hint">Mes services</span><b>{{ $stats['services_total'] }}</b></div>
        <div class="stat-tile"><span class="form-hint">Rendez-vous en attente</span><b>{{ $stats['rdv_en_attente'] }}</b></div>
        <div class="stat-tile"><span class="form-hint">Rendez-vous réalisés</span><b>{{ $stats['rdv_realises'] }}</b></div>
        <div class="stat-tile"><span class="form-hint">Formule</span><b>{{ $stats['abonnement_formule'] }}</b></div>
      @endif

      @if ($user->role === 'eleveur')
        <div class="stat-tile"><span class="form-hint">Rendez-vous à venir</span><b>{{ $stats['mes_rdv_a_venir'] }}</b></div>
      @endif

      <div class="stat-tile"><span class="form-hint">Mes commandes en cours</span><b>{{ $stats['mes_commandes_en_cours'] }}</b></div>
      <div class="stat-tile"><span class="form-hint">Total de mes commandes</span><b>{{ $stats['mes_commandes_total'] }}</b></div>
    </div>

    {{-- Graphiques --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px;margin-bottom:40px;">
      @if (isset($graphiques['commandes_recues']))
        <div class="dash-card">
          <h3 style="font-size:1rem;margin-bottom:16px;">Commandes reçues — 6 derniers mois</h3>
          <canvas id="chartCommandesRecues" height="200"></canvas>
        </div>
      @endif

      @if (isset($graphiques['annonces_par_statut']))
        <div class="dash-card">
          <h3 style="font-size:1rem;margin-bottom:16px;">Mes annonces par statut</h3>
          <canvas id="chartAnnoncesStatut" height="200"></canvas>
        </div>
      @endif

      @if (isset($graphiques['mes_livraisons']))
        <div class="dash-card">
          <h3 style="font-size:1rem;margin-bottom:16px;">Mes livraisons — 6 derniers mois</h3>
          <canvas id="chartLivraisons" height="200"></canvas>
        </div>
      @endif

      @if (isset($graphiques['mes_rdv']))
        <div class="dash-card">
          <h3 style="font-size:1rem;margin-bottom:16px;">Mes rendez-vous — 6 derniers mois</h3>
          <canvas id="chartRdv" height="200"></canvas>
        </div>
      @endif

      <div class="dash-card">
        <h3 style="font-size:1rem;margin-bottom:16px;">Mes achats — 6 derniers mois</h3>
        <canvas id="chartMesAchats" height="200"></canvas>
      </div>
    </div>

    {{-- Raccourcis --}}
    <h3 style="font-size:1.1rem;margin-bottom:16px;">Raccourcis</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;">

      @if ($user->estFournisseur())
        <a href="{{ route('mon-espace.annonces.index') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
          <b>Mes annonces</b>
          <p class="form-hint" style="margin-top:6px;">Publier, modifier, suivre le statut.</p>
        </a>
        <a href="{{ route('mon-espace.commandes-recues.index') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
          <b>Commandes reçues</b>
          <p class="form-hint" style="margin-top:6px;">Traiter les commandes sur mes annonces.</p>
        </a>
      @endif

      @if ($user->role === 'livreur')
        <a href="{{ route('mon-espace.livraisons.proposees') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
          <b>Livraisons proposées</b>
          <p class="form-hint" style="margin-top:6px;">Accepter ou refuser une livraison qui vous est assignée.</p>
        </a>
        <a href="{{ route('mon-espace.livraisons.mes') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
          <b>Mes livraisons</b>
          <p class="form-hint" style="margin-top:6px;">Suivre mes courses en cours.</p>
        </a>
      @endif

      @if ($user->role === 'veterinaire')
        <a href="{{ route('mon-espace.services.index') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
          <b>Mes services</b>
          <p class="form-hint" style="margin-top:6px;">Gérer mes prestations et tarifs.</p>
        </a>
        <a href="{{ route('mon-espace.rendez-vous-recus.index') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
          <b>Rendez-vous reçus</b>
          <p class="form-hint" style="margin-top:6px;">Confirmer, refuser, clôturer.</p>
        </a>
        <a href="{{ route('mon-espace.abonnement.show') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
          <b>Mon abonnement</b>
          <p class="form-hint" style="margin-top:6px;">Basique / Premium.</p>
        </a>
      @endif

      @if ($user->role === 'eleveur')
        <a href="{{ route('mon-espace.rendez-vous.index') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
          <b>Mes rendez-vous</b>
          <p class="form-hint" style="margin-top:6px;">Suivre mes demandes de consultation.</p>
        </a>
        <a href="{{ route('veterinaires.index') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
          <b>Trouver un vétérinaire</b>
          <p class="form-hint" style="margin-top:6px;">Prendre un nouveau rendez-vous.</p>
        </a>
      @endif

      {{-- Commun à tous les rôles : tout utilisateur peut acheter (annonces
           dont il n'est pas propriétaire) — voir CommandePolicy::create(). --}}
      <a href="{{ route('mon-espace.commandes.index') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
        <b>Mes commandes</b>
        <p class="form-hint" style="margin-top:6px;">Suivre mes achats sur ElevConnect.</p>
      </a>
      <a href="{{ route('catalogue.index') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
        <b>Parcourir les annonces</b>
        <p class="form-hint" style="margin-top:6px;">Animaux, provendes, accessoires.</p>
      </a>
      @if ($user->peutPublierActualite())
        <a href="{{ route('mon-espace.actualites.create') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
          <b>Publier une actualité</b>
          <p class="form-hint" style="margin-top:6px;">Partager un conseil ou une nouvelle.</p>
        </a>
      @endif
      <a href="{{ route('mon-espace.notifications.index') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
        <b>Notifications</b>
        <p class="form-hint" style="margin-top:6px;">Historique de mes alertes.</p>
      </a>
      <a href="{{ route('mon-espace.profil.edit') }}" class="dash-card" style="margin-bottom:0;text-decoration:none;color:inherit;">
        <b>Mon profil</b>
        <p class="form-hint" style="margin-top:6px;">Informations personnelles et mot de passe.</p>
      </a>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function(){
  const graphiques = @json($graphiques);
  const couleurPrincipale = '#BD4A1E';
  const couleurSecondaire = '#2F4F38';
  const couleursDoughnut = ['#4C7854', '#D79B2A', '#BD4A1E'];

  Chart.defaults.font.family = "'Manrope', sans-serif";
  Chart.defaults.color = '#5B6B60';

  function barChart(id, serie, couleur){
    const el = document.getElementById(id);
    if (!el || !serie) return;
    new Chart(el, {
      type: 'bar',
      data: {
        labels: serie.labels,
        datasets: [{ data: serie.valeurs, backgroundColor: couleur, borderRadius: 6, maxBarThickness: 42 }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }

  function doughnutChart(id, serie){
    const el = document.getElementById(id);
    if (!el || !serie) return;
    new Chart(el, {
      type: 'doughnut',
      data: {
        labels: serie.labels,
        datasets: [{ data: serie.valeurs, backgroundColor: couleursDoughnut }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }

  barChart('chartCommandesRecues', graphiques.commandes_recues, couleurPrincipale);
  doughnutChart('chartAnnoncesStatut', graphiques.annonces_par_statut);
  barChart('chartLivraisons', graphiques.mes_livraisons, couleurSecondaire);
  barChart('chartRdv', graphiques.mes_rdv, couleurSecondaire);
  barChart('chartMesAchats', graphiques.mes_achats, couleurPrincipale);
})();
</script>
@endpush
