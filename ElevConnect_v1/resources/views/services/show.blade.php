@extends('layouts.mon-espace')

@section('title', $service->titre_service.' — ElevConnect')

@section('content')
<a href="{{ route('mon-espace.services.index') }}" class="me-back">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
  Retour à mes services
</a>

<div class="dash-card" style="max-width:700px;">
  @if ($service->photo_illustrative)
    <img src="{{ asset('storage/'.$service->photo_illustrative) }}" alt="{{ $service->titre_service }}"
         style="width:100%;max-height:300px;object-fit:cover;border-radius:var(--radius-sm);margin-bottom:24px;">
  @endif

  <span class="status-pill {{ $service->statut_service === 'disponible' ? 'visible' : 'rejetee' }}" style="margin-bottom:10px;display:inline-block;">
    {{ ucfirst($service->statut_service) }}
  </span>

  <h1 style="margin-bottom:8px;">{{ $service->titre_service }}</h1>
  <div class="price" style="font-size:1.3rem;margin-bottom:20px;">{{ number_format($service->prix, 0, ',', ' ') }} FCFA</div>

  @if ($service->description)
    <p style="color:var(--ink-soft);margin-bottom:20px;">{{ $service->description }}</p>
  @endif

  <div class="form-grid" style="margin-bottom:24px;">
    <div><b>Durée estimée :</b> {{ $service->temps_traitement }} minutes</div>
    <div><b>Rendez-vous liés :</b> {{ $service->rendezVous->count() }}</div>
  </div>

  <div class="dash-actions">
    <a href="{{ route('mon-espace.services.edit', $service) }}" class="btn btn-ghost-dark btn-sm">Modifier</a>
    <form method="POST" action="{{ route('mon-espace.services.destroy', $service) }}" onsubmit="return confirm('Supprimer ce service ?');">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
    </form>
  </div>
</div>
@endsection
