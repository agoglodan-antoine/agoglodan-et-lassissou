@extends('layouts.monEspace')

@section('title', 'Mon tableau de bord — ElevConnect')

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
        <h1>Bonjour {{ $user->prenom }}</h1>
        <p>{{ $roleLabels[$user->role] ?? $user->role }} — voici un aperçu de votre activité.</p>
      </div>
      @if ($stats['notifications_non_lues'] > 0)
        <a href="{{ route('mon-espace.notifications.index') }}" class="btn btn-ghost-dark"><i class="fa-solid fa-bell"></i> {{ $stats['notifications_non_lues'] }} nouvelle(s) notification(s)</a>
      @endif
    </div>

    {{-- Indicateurs --}}
    <div class="stat-strip">
      @if ($user->estFournisseur())
        <div class="stat-chip"><span class="label">Mon catalogue</span><b>{{ $stats['annonces_total'] }}</b></div>
        <div class="stat-chip"><span class="label">Visibles</span><b>{{ $stats['annonces_visibles'] }}</b></div>
        <div class="stat-chip"><span class="label">En modération</span><b>{{ $stats['annonces_en_attente'] }}</b></div>
        <div class="stat-chip"><span class="label">À traiter</span><b>{{ $stats['commandes_a_traiter'] }}</b></div>
        <div class="stat-chip"><span class="label">Ventes confirmées</span><b>{{ $stats['commandes_confirmees'] }}</b></div>
        <div class="stat-chip"><span class="label">Chiffre d'affaires net</span><b>{{ number_format($stats['chiffre_affaires'], 0, ',', ' ') }} <small>FCFA</small></b></div>
      @endif

      @if ($user->role === 'livreur')
        <div class="stat-chip"><span class="label">Proposées</span><b>{{ $stats['livraisons_proposees'] }}</b></div>
        <div class="stat-chip"><span class="label">En cours</span><b>{{ $stats['mes_livraisons_en_cours'] }}</b></div>
        <div class="stat-chip"><span class="label">Terminées</span><b>{{ $stats['mes_livraisons_terminees'] }}</b></div>
        <div class="stat-chip"><span class="label">Revenus livraison</span><b>{{ number_format($stats['revenus_livraison'], 0, ',', ' ') }} <small>FCFA</small></b></div>
      @endif

      @if ($user->role === 'veterinaire')
        <div class="stat-chip"><span class="label">Mes services</span><b>{{ $stats['services_total'] }}</b></div>
        <div class="stat-chip"><span class="label">RDV en attente</span><b>{{ $stats['rdv_en_attente'] }}</b></div>
        <div class="stat-chip"><span class="label">RDV réalisés</span><b>{{ $stats['rdv_realises'] }}</b></div>
        <div class="stat-chip"><span class="label">Formule</span><b>{{ $stats['abonnement_formule'] }}</b></div>
      @endif

      @if ($user->role === 'eleveur')
        <div class="stat-chip"><span class="label">RDV à venir</span><b>{{ $stats['mes_rdv_a_venir'] }}</b></div>
      @endif

      <div class="stat-chip"><span class="label">Commandes en cours</span><b>{{ $stats['mes_commandes_en_cours'] }}</b></div>
      <div class="stat-chip"><span class="label">Total commandes</span><b>{{ $stats['mes_commandes_total'] }}</b></div>
    </div>

    {{-- Graphiques --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:18px;margin-bottom:36px;">
      @if (isset($graphiques['commandes_recues']))
        <div class="chart-card">
          <h3>Commandes reçues — 6 derniers mois</h3>
          <div class="chart-card-body"><canvas id="chartCommandesRecues"></canvas></div>
        </div>
      @endif

      @if (isset($graphiques['annonces_par_statut']))
        <div class="chart-card">
          <h3>Mon catalogue par statut</h3>
          <div class="chart-card-body"><canvas id="chartAnnoncesStatut"></canvas></div>
        </div>
      @endif

      @if (isset($graphiques['mes_livraisons']))
        <div class="chart-card">
          <h3>Mes livraisons — 6 derniers mois</h3>
          <div class="chart-card-body"><canvas id="chartLivraisons"></canvas></div>
        </div>
      @endif

      @if (isset($graphiques['mes_rdv']))
        <div class="chart-card">
          <h3>Mes rendez-vous — 6 derniers mois</h3>
          <div class="chart-card-body"><canvas id="chartRdv"></canvas></div>
        </div>
      @endif

      <div class="chart-card">
        <h3>Mes achats — 6 derniers mois</h3>
        <div class="chart-card-body"><canvas id="chartMesAchats"></canvas></div>
      </div>
    </div>

    {{-- Raccourcis --}}
    <h3 style="font-size:0.95rem;font-weight:700;margin-bottom:14px;">Raccourcis</h3>
    <div class="shortcut-grid">

      @if ($user->estFournisseur())
        <a href="{{ route('mon-espace.annonces.index') }}" class="shortcut-card">
          <i class="fa-solid fa-box"></i>
          <b>Mon catalogue</b>
          <p>Publier, modifier, suivre le statut.</p>
        </a>
        <a href="{{ route('mon-espace.commandes-fournisseur.index') }}" class="shortcut-card">
          <i class="fa-solid fa-receipt"></i>
          <b>Commandes reçues</b>
          <p>Traiter les commandes sur Mon catalogue.</p>
        </a>
      @endif

      @if ($user->role === 'livreur')
        <a href="{{ route('mon-espace.livraison.proposees') }}" class="shortcut-card">
          <i class="fa-solid fa-truck"></i>
          <b>Livraisons proposées</b>
          <p>Accepter ou refuser une livraison assignée.</p>
        </a>
        <a href="{{ route('mon-espace.livraison.mes') }}" class="shortcut-card">
          <i class="fa-solid fa-route"></i>
          <b>Mes livraisons</b>
          <p>Suivre mes courses en cours.</p>
        </a>
      @endif

      @if ($user->role === 'veterinaire')
        <a href="{{ route('mon-espace.services.index') }}" class="shortcut-card">
          <i class="fa-solid fa-stethoscope"></i>
          <b>Mes services</b>
          <p>Gérer mes prestations et tarifs.</p>
        </a>
        <a href="{{ route('mon-espace.rendez-vous-recus.index') }}" class="shortcut-card">
          <i class="fa-solid fa-calendar-days"></i>
          <b>Rendez-vous reçus</b>
          <p>Confirmer, refuser, clôturer.</p>
        </a>
        <a href="{{ route('mon-espace.abonnement.show') }}" class="shortcut-card">
          <i class="fa-solid fa-star"></i>
          <b>Mon abonnement</b>
          <p>Basique / Premium.</p>
        </a>
      @endif

      @if ($user->role === 'eleveur')
        <a href="{{ route('mon-espace.rendez-vous.index') }}" class="shortcut-card">
          <i class="fa-solid fa-calendar-days"></i>
          <b>Mes rendez-vous</b>
          <p>Suivre mes demandes de consultation.</p>
        </a>
        <a href="{{ route('veterinaires.index') }}" class="shortcut-card">
          <i class="fa-solid fa-magnifying-glass"></i>
          <b>Trouver un vétérinaire</b>
          <p>Prendre un nouveau rendez-vous.</p>
        </a>
      @endif

      {{-- Commun à tous les rôles : tout utilisateur peut acheter (annonces
           dont il n'est pas propriétaire) — voir CommandePolicy::create(). --}}
      <a href="{{ route('mon-espace.commandes.index') }}" class="shortcut-card">
        <i class="fa-solid fa-cart-shopping"></i>
        <b>Mes achats</b>
        <p>Suivre mes achats sur ElevConnect.</p>
      </a>
      <a href="{{ route('catalogue.index') }}" class="shortcut-card">
        <i class="fa-solid fa-store"></i>
        <b>Parcourir les annonces</b>
        <p>Animaux, provendes, accessoires.</p>
      </a>
      @if ($user->peutPublierActualite())
        <a href="{{ route('mon-espace.actualites.create') }}" class="shortcut-card">
          <i class="fa-solid fa-newspaper"></i>
          <b>Publier une actualité</b>
          <p>Partager un conseil ou une nouvelle.</p>
        </a>
      @endif
      <a href="{{ route('mon-espace.notifications.index') }}" class="shortcut-card">
        <i class="fa-solid fa-bell"></i>
        <b>Notifications</b>
        <p>Historique de mes alertes.</p>
      </a>
      <a href="{{ route('mon-espace.profile.edit') }}" class="shortcut-card">
        <i class="fa-solid fa-user"></i>
        <b>Mon profil</b>
        <p>Informations personnelles et mot de passe.</p>
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
  Chart.defaults.font.size = 11;
  Chart.defaults.color = '#5B6B60';

  function barChart(id, serie, couleur){
    const el = document.getElementById(id);
    if (!el || !serie) return;
    new Chart(el, {
      type: 'bar',
      data: {
        labels: serie.labels,
        datasets: [{ data: serie.valeurs, backgroundColor: couleur, borderRadius: 5, maxBarThickness: 32 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
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
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12 } } }
      }
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
