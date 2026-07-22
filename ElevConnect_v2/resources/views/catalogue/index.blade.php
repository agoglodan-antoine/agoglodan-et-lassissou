@extends('layouts.app')

@section('title', 'Annonces — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Toutes les annonces</h1>
        <p>Animaux, provendes et accessoires publiés par nos fournisseurs, validés par nos administrateurs.</p>
      </div>
    </div>

    <form class="filter-bar" method="GET" action="{{ route('catalogue.index') }}" id="filterForm">
      <div class="form-field">
        <label for="type">Type</label>
        <select id="type" name="type">
          <option value="">Tous types</option>
          <option value="animal" {{ ($filtres['type'] ?? '') === 'animal' ? 'selected' : '' }}>Animaux</option>
          <option value="provende" {{ ($filtres['type'] ?? '') === 'provende' ? 'selected' : '' }}>Provendes</option>
          <option value="accessoire" {{ ($filtres['type'] ?? '') === 'accessoire' ? 'selected' : '' }}>Accessoires</option>
        </select>
      </div>
      <div class="form-field">
        <label for="prix_max">Prix max. (FCFA)</label>
        <input type="number" id="prix_max" name="prix_max" min="0" value="{{ $filtres['prix_max'] ?? '' }}">
      </div>
      <div class="form-field">
        <label for="rayon">Rayon (km)</label>
        <select id="rayon" name="rayon">
          @foreach ([10, 25, 50, 100] as $r)
            <option value="{{ $r }}" {{ (int) ($filtres['rayon'] ?? 50) === $r ? 'selected' : '' }}>{{ $r }} km</option>
          @endforeach
        </select>
      </div>
      <input type="hidden" name="lat" id="latInput" value="{{ request('lat') }}">
      <input type="hidden" name="lng" id="lngInput" value="{{ request('lng') }}">
      <button type="button" class="btn btn-ghost-dark" id="useLocationBtn">
        <i class="fa-solid fa-location-dot"></i> {{ $geolocalise ? 'Position activée' : 'Autour de moi' }}
      </button>
      <button type="submit" class="btn btn-primary">Filtrer</button>
    </form>

    @if ($annonces->isEmpty())
      <div class="empty-state">Aucune annonce ne correspond à votre recherche pour le moment.</div>
    @else
      <div class="catalogue-grid">
        @foreach ($annonces as $annonce)
          <div class="catalogue-card">
            <a href="{{ route('catalogue.show', $annonce->id_annonce) }}" style="text-decoration:none;color:inherit;display:block;">
              <img src="{{ $annonce->image_1 ? asset('storage/'.$annonce->image_1) : '' }}" alt="{{ $annonce->titre }}">
            </a>
            <div class="body">
              <a href="{{ route('catalogue.show', $annonce->id_annonce) }}" style="text-decoration:none;color:inherit;">
                <h4>{{ $annonce->titre }}</h4>
              </a>
              <div class="meta">
                {{ ucfirst($annonce->type_annonce) }} · {{ $annonce->nom }} {{ $annonce->prenom }}
                @isset($annonce->distance_km)
                  · {{ number_format($annonce->distance_km, 1) }} km
                @endisset
              </div>
              <div class="price">{{ number_format($annonce->prix_unitaire, 0, ',', ' ') }} FCFA</div>
              @if (! auth()->check() || auth()->id() !== $annonce->id_utilisateur)
                <a href="{{ route('commandes.create', $annonce->id_annonce) }}" class="btn btn-primary btn-sm" style="width:100%;text-align:center;justify-content:center;margin-top:10px;">Commander</a>
              @endif
            </div>
          </div>
        @endforeach
      </div>

      <div style="margin-top:32px;">{{ $annonces->links() }}</div>
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
