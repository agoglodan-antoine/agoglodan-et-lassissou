@extends('layouts.app')

@section('title', 'Actualités — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Actualités</h1>
        <p>Conseils d'élevage, santé animale et consommation locale, partagés par la communauté.</p>
      </div>
      @auth
        @if (auth()->user()->peutPublierActualite())
          <a href="{{ route('mon-espace.actualites.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Publier une actualité</a>
        @endif
      @endauth
    </div>

    <form class="filter-bar" method="GET" action="{{ route('actualites.index') }}">
      <div class="form-field">
        <label for="q">Recherche</label>
        <input type="text" id="q" name="q" placeholder="Mot-clé dans le titre ou le contenu" value="{{ $filtres['q'] ?? '' }}">
      </div>
      <div class="form-field">
        <label for="role">Auteur</label>
        <select id="role" name="role">
          <option value="">Tous les rôles</option>
          <option value="eleveur" {{ ($filtres['role'] ?? '') === 'eleveur' ? 'selected' : '' }}>Éleveur</option>
          <option value="vendeur_provende" {{ ($filtres['role'] ?? '') === 'vendeur_provende' ? 'selected' : '' }}>Vendeur de provende</option>
          <option value="vendeur_accessoire" {{ ($filtres['role'] ?? '') === 'vendeur_accessoire' ? 'selected' : '' }}>Vendeur d'accessoires</option>
          <option value="veterinaire" {{ ($filtres['role'] ?? '') === 'veterinaire' ? 'selected' : '' }}>Vétérinaire</option>
          <option value="administrateur" {{ ($filtres['role'] ?? '') === 'administrateur' ? 'selected' : '' }}>Administration</option>
        </select>
      </div>
      <div class="form-field">
        <label for="tri">Tri</label>
        <select id="tri" name="tri">
          <option value="recent" {{ ($filtres['tri'] ?? 'recent') === 'recent' ? 'selected' : '' }}>Plus récentes</option>
          <option value="ancien" {{ ($filtres['tri'] ?? '') === 'ancien' ? 'selected' : '' }}>Plus anciennes</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Filtrer</button>
    </form>

    @if (session('status'))
      <div class="dash-card" style="background:var(--pasture-soft);border:none;color:var(--pasture-dark);font-weight:600;">
        {{ session('status') }}
      </div>
    @endif

    @if ($actualites->isEmpty())
      <div class="empty-state">Aucune actualité publiée pour le moment.</div>
    @else
      <div class="catalogue-grid">
        @foreach ($actualites as $actualite)
          <a href="{{ route('actualites.show', $actualite) }}" style="text-decoration:none;color:inherit;">
            <div class="catalogue-card">
              @if ($actualite->medias->isNotEmpty())
                <img src="{{ asset('storage/'.$actualite->medias->first()->chemin_fichier) }}" alt="{{ $actualite->titre }}">
              @endif
              <div class="body">
                <h4>{{ $actualite->titre }}</h4>
                <div class="meta">
                  Par {{ $actualite->auteur->nom }} {{ $actualite->auteur->prenom }} —
                  {{ $actualite->date_publication->format('d/m/Y') }}
                </div>
                <p style="font-size:0.88rem;color:var(--ink-soft);">{{ \Illuminate\Support\Str::limit($actualite->contenu, 100) }}</p>
              </div>
            </div>
          </a>
        @endforeach
      </div>

      <div style="margin-top:32px;">{{ $actualites->links() }}</div>
    @endif
  </div>
</section>
@endsection
