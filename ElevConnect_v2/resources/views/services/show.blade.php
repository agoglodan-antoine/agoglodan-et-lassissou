@extends('layouts.monEspace')

@section('title', $service->titre_service.' — ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:700px;">
    <div class="dash-card">
      <x-back-link :href="route('mon-espace.services.index')" label="Retour à mes services" />

      @if (session('status'))
        <div class="dash-card" style="background:var(--pasture-soft);box-shadow:none;color:var(--pasture-dark);font-weight:600;">
          {{ session('status') }}
        </div>
      @endif

      <div class="dash-head" style="margin-bottom:20px;">
        <div>
          <h1 style="font-size:1.5rem;">{{ $service->titre_service }}</h1>
        </div>
        <x-status-pill :status="$service->statut_service" />
      </div>

      @if ($service->photo_illustrative)
        <img src="{{ asset('storage/'.$service->photo_illustrative) }}" alt="" style="width:100%;max-height:280px;object-fit:cover;border-radius:var(--radius-sm);margin-bottom:20px;background:var(--sand);">
      @endif

      @if ($service->description)
        <p style="color:var(--ink-soft);margin-bottom:20px;">{{ $service->description }}</p>
      @endif

      <div class="form-grid" style="margin-bottom:24px;">
        <div class="form-field">
          <label>Prix</label>
          <p><b>{{ number_format($service->prix, 0, ',', ' ') }} FCFA</b></p>
        </div>
        <div class="form-field">
          <label>Durée estimée</label>
          <p><b>{{ $service->temps_traitement }} min</b></p>
        </div>
      </div>

      <div class="dash-actions">
        <a href="{{ route('mon-espace.services.edit', $service) }}" class="btn btn-primary">Modifier</a>
        <form method="POST" action="{{ route('mon-espace.services.destroy', $service) }}" onsubmit="return confirm('Supprimer ce service ?');">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
