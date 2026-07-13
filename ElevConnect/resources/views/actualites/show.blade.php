@extends('layouts.app')

@section('title', $actualite->titre.' — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:800px;">
    <div class="dash-card">
      @if ($actualite->medias->isNotEmpty())
        <img src="{{ asset('storage/'.$actualite->medias->first()->chemin_fichier) }}" alt="{{ $actualite->titre }}"
             style="width:100%;max-height:380px;object-fit:cover;border-radius:var(--radius-sm);margin-bottom:24px;">
      @endif

      <h1 style="margin-bottom:8px;">{{ $actualite->titre }}</h1>
      <div class="meta" style="margin-bottom:20px;">
        Par {{ $actualite->auteur->nom }} {{ $actualite->auteur->prenom }} — {{ $actualite->date_publication->format('d/m/Y') }}
      </div>

      <p style="white-space:pre-line;line-height:1.7;">{{ $actualite->contenu }}</p>

      @if ($actualite->medias->count() > 1)
        <div class="catalogue-grid" style="margin-top:24px;">
          @foreach ($actualite->medias->skip(1) as $media)
            <img src="{{ asset('storage/'.$media->chemin_fichier) }}" style="width:100%;border-radius:var(--radius-sm);">
          @endforeach
        </div>
      @endif

      @can('update', $actualite)
        <div class="dash-actions" style="margin-top:28px;">
          <a href="{{ route('actualites.edit', $actualite) }}" class="btn btn-ghost-dark btn-sm">Modifier</a>
          <form method="POST" action="{{ route('actualites.destroy', $actualite) }}" onsubmit="return confirm('Supprimer cette actualité ?');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
          </form>
        </div>
      @endcan
    </div>
  </div>
</section>
@endsection
