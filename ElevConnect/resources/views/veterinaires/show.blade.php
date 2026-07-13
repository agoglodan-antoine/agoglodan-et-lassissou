@extends('layouts.app')

@section('title', 'Dr '.$veterinaire->utilisateur->nom.' — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:800px;">
    <div class="dash-card">
      <div class="dash-head" style="margin-bottom:20px;">
        <div>
          <h1>Dr {{ $veterinaire->utilisateur->nom }} {{ $veterinaire->utilisateur->prenom }}</h1>
          <p>{{ $veterinaire->specialite ?? 'Généraliste' }} — {{ $veterinaire->zone_intervention ?? 'Zone non renseignée' }}</p>
          @if ($veterinaire->note_moyenne)
            <p style="color:var(--clay-dark);font-weight:700;margin-top:6px;">★ {{ number_format($veterinaire->note_moyenne, 1) }} ({{ $veterinaire->nombre_avis }} avis)</p>
          @endif
        </div>
        @auth
          @if (auth()->user()->role === \App\Models\Utilisateur::ROLE_ELEVEUR)
            <a href="{{ route('rendez-vous.create', $veterinaire) }}" class="btn btn-primary">Prendre rendez-vous</a>
          @endif
        @else
          <a href="{{ route('login') }}" class="btn btn-primary">Se connecter pour prendre RDV</a>
        @endauth
      </div>

      <h3 style="font-size:1.1rem;margin-bottom:14px;">Services proposés</h3>

      @if ($veterinaire->services->isEmpty())
        <p class="form-hint">Aucun service publié pour le moment.</p>
      @else
        <div class="catalogue-grid">
          @foreach ($veterinaire->services as $service)
            <div class="catalogue-card">
              <img src="{{ $service->photo_illustrative ? asset('storage/'.$service->photo_illustrative) : '' }}" alt="{{ $service->titre_service }}">
              <div class="body">
                <h4>{{ $service->titre_service }}</h4>
                <div class="meta">{{ $service->temps_traitement }} min</div>
                <div class="price">{{ number_format($service->prix, 0, ',', ' ') }} FCFA</div>
              </div>
            </div>
          @endforeach
        </div>
      @endif

      <p class="form-hint" style="margin-top:24px;">
        Le paiement de la consultation se règle directement avec le vétérinaire,
        hors plateforme — ElevConnect facilite uniquement la prise de rendez-vous.
      </p>
    </div>
  </div>
</section>
@endsection
