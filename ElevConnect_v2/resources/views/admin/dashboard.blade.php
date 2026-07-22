@extends('layouts.monEspace')

@section('title', 'Administration — ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Vue d'ensemble</h1>
        <p>Indicateurs clés de la plateforme.</p>
      </div>
    </div>

    @include('admin._nav')

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:32px;">
      <div class="dash-card" style="margin-bottom:0;">
        <div class="form-hint">Annonces en attente</div>
        <div style="font-size:2rem;font-weight:800;">{{ $stats['annonces_en_attente'] }}</div>
        <a href="{{ route('mon-espace.admin.moderation.index') }}" style="font-size:0.85rem;font-weight:700;color:var(--clay-dark);">Modérer <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="dash-card" style="margin-bottom:0;">
        <div class="form-hint">Utilisateurs actifs</div>
        <div style="font-size:2rem;font-weight:800;">{{ $stats['utilisateurs_actifs'] }}</div>
        <a href="{{ route('mon-espace.admin.utilisateurs.index') }}" style="font-size:0.85rem;font-weight:700;color:var(--clay-dark);">Gérer <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="dash-card" style="margin-bottom:0;">
        <div class="form-hint">Comptes suspendus</div>
        <div style="font-size:2rem;font-weight:800;">{{ $stats['utilisateurs_suspendus'] }}</div>
      </div>
      <div class="dash-card" style="margin-bottom:0;">
        <div class="form-hint">Commandes confirmées</div>
        <div style="font-size:2rem;font-weight:800;">{{ $stats['commandes_confirmees'] }}</div>
      </div>
      <div class="dash-card" style="margin-bottom:0;">
        <div class="form-hint">Volume d'affaires (net)</div>
        <div style="font-size:2rem;font-weight:800;">{{ number_format($stats['volume_affaires'], 0, ',', ' ') }} <small style="font-size:1rem;">FCFA</small></div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px;">
      <div class="dash-card">
        <h3 style="font-size:1rem;margin-bottom:16px;">Nouveaux utilisateurs — 6 derniers mois</h3>
        <canvas id="chartNouveauxUtilisateurs" height="200"></canvas>
      </div>
      <div class="dash-card">
        <h3 style="font-size:1rem;margin-bottom:16px;">Commandes — 6 derniers mois</h3>
        <canvas id="chartCommandes" height="200"></canvas>
      </div>
      <div class="dash-card">
        <h3 style="font-size:1rem;margin-bottom:16px;">Utilisateurs par rôle</h3>
        <canvas id="chartUtilisateursRole" height="220"></canvas>
      </div>
      <div class="dash-card">
        <h3 style="font-size:1rem;margin-bottom:16px;">Annonces par statut</h3>
        <canvas id="chartAnnoncesStatut" height="220"></canvas>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function(){
  const graphiques = @json($graphiques);
  const palette = ['#2F4F38', '#4C7854', '#D79B2A', '#BD4A1E', '#8a6414', '#5B6B60', '#1E3626'];

  Chart.defaults.font.family = "'Manrope', sans-serif";
  Chart.defaults.color = '#5B6B60';

  new Chart(document.getElementById('chartNouveauxUtilisateurs'), {
    type: 'bar',
    data: {
      labels: graphiques.nouveaux_utilisateurs.labels,
      datasets: [{ data: graphiques.nouveaux_utilisateurs.valeurs, backgroundColor: '#4C7854', borderRadius: 6, maxBarThickness: 42 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });

  new Chart(document.getElementById('chartCommandes'), {
    type: 'line',
    data: {
      labels: graphiques.commandes.labels,
      datasets: [{ data: graphiques.commandes.valeurs, borderColor: '#BD4A1E', backgroundColor: 'rgba(189,74,30,0.12)', fill: true, tension: 0.35 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });

  new Chart(document.getElementById('chartUtilisateursRole'), {
    type: 'doughnut',
    data: {
      labels: graphiques.utilisateurs_par_role.labels,
      datasets: [{ data: graphiques.utilisateurs_par_role.valeurs, backgroundColor: palette }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });

  new Chart(document.getElementById('chartAnnoncesStatut'), {
    type: 'doughnut',
    data: {
      labels: graphiques.annonces_par_statut.labels,
      datasets: [{ data: graphiques.annonces_par_statut.valeurs, backgroundColor: ['#4C7854', '#D79B2A', '#BD4A1E'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });
})();
</script>
@endpush
