@extends('layouts.app')

@section('title', 'Vétérinaires — ElevConnect')

@push('styles')
  
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Vétérinaires</h1>
        <p>Trouvez un vétérinaire près de chez vous et prenez rendez-vous en ligne.</p>
      </div>
    </div>

    <form class="filter-bar" method="GET" action="{{ route('veterinaires.index') }}" id="filterForm">
      <div class="form-field">
        <label for="specialite">Spécialité</label>
        <input type="text" id="specialite" name="specialite" value="{{ request('specialite') }}" placeholder="Ex. bovins, volailles…">
      </div>
      <input type="hidden" name="lat" id="latInput" value="{{ request('lat') }}">
      <input type="hidden" name="lng" id="lngInput" value="{{ request('lng') }}">
      <button type="button" class="btn btn-ghost-dark" id="useLocationBtn">
        <i class="fa-solid fa-location-dot"></i> {{ $geolocalise ? 'Position activée' : 'Autour de moi' }}
      </button>
      <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>

    @if ($veterinaires->isEmpty())
      <div class="empty-state">Aucun vétérinaire ne correspond à votre recherche pour le moment.</div>
    @else
      <div class="catalogue-grid">
        @foreach ($veterinaires as $vet)
          <a href="{{ route('veterinaires.show', $vet->id_utilisateur) }}" style="text-decoration:none;color:inherit;">
            <div class="catalogue-card">
              <div class="body">
                @if ($vet->est_premium)
                  <x-status-pill force="visible" label="Premium" style="margin-bottom:8px;" />
                @endif
                <h4>Dr {{ $vet->nom }} {{ $vet->prenom }}</h4>
                <div class="meta">
                  {{ $vet->specialite ?? 'Généraliste' }}
                  @isset($vet->distance_km)
                    · {{ number_format($vet->distance_km, 1) }} km
                  @endisset
                </div>
                @if ($vet->note_moyenne)
                  <div class="price"><i class="fa-solid fa-star"></i> {{ number_format($vet->note_moyenne, 1) }} ({{ $vet->nombre_avis }} avis)</div>
                @else
                  <div class="meta">Pas encore d'avis</div>
                @endif
              </div>
            </div>
          </a>
        @endforeach
      </div>

      <div style="margin-top:32px;">{{ $veterinaires->links() }}</div>
    @endif
  </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('useLocationBtn').addEventListener('click', function(){
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(function(pos){
    document.getElementById('latInput').value = pos.coords.latitude;
    document.getElementById('lngInput').value = pos.coords.longitude;
    document.getElementById('filterForm').submit();
  });
});
</script>
@endpush
