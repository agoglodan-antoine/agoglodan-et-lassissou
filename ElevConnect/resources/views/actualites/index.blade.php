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
          <a href="{{ route('actualites.create') }}" class="btn btn-primary">+ Publier une actualité</a>
        @endif
      @endauth
    </div>

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
