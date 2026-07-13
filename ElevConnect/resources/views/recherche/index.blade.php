@extends('layouts.app')

@section('title', 'Recherche — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Résultats de recherche</h1>
        @if ($q !== '')
          <p>{{ $total }} résultat(s) pour « {{ $q }} »</p>
        @else
          <p>Saisissez un terme à rechercher.</p>
        @endif
      </div>
    </div>

    @if ($q === '')
      <div class="empty-state">Utilisez le bouton de recherche dans le menu pour lancer une recherche.</div>
    @elseif ($total === 0)
      <div class="empty-state">Aucun résultat pour « {{ $q }} ». Essayez un autre terme.</div>
    @else

      @if ($annonces->isNotEmpty())
        <h3 style="font-size:1.1rem;margin-bottom:14px;">Annonces ({{ $annonces->count() }})</h3>
        <div class="catalogue-grid" style="margin-bottom:36px;">
          @foreach ($annonces as $annonce)
            <a href="{{ route('catalogue.show', $annonce) }}" style="text-decoration:none;color:inherit;">
              <div class="catalogue-card">
                <img src="{{ $annonce->image_1 ? asset('storage/'.$annonce->image_1) : '' }}" alt="{{ $annonce->titre }}">
                <div class="body">
                  <h4>{{ $annonce->titre }}</h4>
                  <div class="meta">{{ ucfirst($annonce->type_annonce) }} · {{ $annonce->auteur->nom }} {{ $annonce->auteur->prenom }}</div>
                  <div class="price">{{ number_format($annonce->prix_unitaire, 0, ',', ' ') }} FCFA</div>
                </div>
              </div>
            </a>
          @endforeach
        </div>
      @endif

      @if ($actualites->isNotEmpty())
        <h3 style="font-size:1.1rem;margin-bottom:14px;">Actualités ({{ $actualites->count() }})</h3>
        <div class="catalogue-grid" style="margin-bottom:36px;">
          @foreach ($actualites as $actualite)
            <a href="{{ route('actualites.show', $actualite) }}" style="text-decoration:none;color:inherit;">
              <div class="catalogue-card">
                <div class="body">
                  <h4>{{ $actualite->titre }}</h4>
                  <div class="meta">Par {{ $actualite->auteur->nom }} {{ $actualite->auteur->prenom }}</div>
                  <p>{{ \Illuminate\Support\Str::limit($actualite->contenu, 90) }}</p>
                </div>
              </div>
            </a>
          @endforeach
        </div>
      @endif

      @if ($services->isNotEmpty())
        <h3 style="font-size:1.1rem;margin-bottom:14px;">Services vétérinaires ({{ $services->count() }})</h3>
        <div class="catalogue-grid" style="margin-bottom:36px;">
          @foreach ($services as $service)
            <a href="{{ route('veterinaires.show', $service->id_veterinaire) }}" style="text-decoration:none;color:inherit;">
              <div class="catalogue-card">
                <img src="{{ $service->photo_illustrative ? asset('storage/'.$service->photo_illustrative) : '' }}" alt="{{ $service->titre_service }}">
                <div class="body">
                  <h4>{{ $service->titre_service }}</h4>
                  <div class="meta">Dr {{ $service->veterinaire->utilisateur->nom }} {{ $service->veterinaire->utilisateur->prenom }}</div>
                  <div class="price">{{ number_format($service->prix, 0, ',', ' ') }} FCFA</div>
                </div>
              </div>
            </a>
          @endforeach
        </div>
      @endif

      @if ($livreurs->isNotEmpty())
        <h3 style="font-size:1.1rem;margin-bottom:14px;">Services de transport ({{ $livreurs->count() }})</h3>
        <div class="catalogue-grid" style="margin-bottom:36px;">
          @foreach ($livreurs as $livreur)
            <div class="catalogue-card">
              <div class="body">
                <h4>{{ $livreur->utilisateur->nom }} {{ $livreur->utilisateur->prenom }}</h4>
                <div class="meta">{{ $livreur->moyen_transport ?? 'Transport' }}</div>
                <p>Zone : {{ $livreur->zone_couverture ?? 'Non renseignée' }}</p>
              </div>
            </div>
          @endforeach
        </div>
      @endif

    @endif
  </div>
</section>
@endsection
