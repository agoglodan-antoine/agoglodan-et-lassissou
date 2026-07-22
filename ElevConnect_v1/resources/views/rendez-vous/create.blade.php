@extends('layouts.app')

@section('title', 'Prendre rendez-vous — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <a href="{{ route('veterinaires.show', $veterinaire) }}" class="back-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Retour au profil du vétérinaire
    </a>
    <div class="auth-card" style="max-width:640px;">
      <h1>Rendez-vous avec Dr {{ $veterinaire->utilisateur->nom }} {{ $veterinaire->utilisateur->prenom }}</h1>
      <p class="sub">Le paiement de la consultation se règle directement avec le vétérinaire, hors plateforme.</p>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('rendez-vous.store', $veterinaire) }}">
        @csrf

        @if ($veterinaire->services->isNotEmpty())
          <div class="form-field">
            <label for="id_service">Service concerné (facultatif)</label>
            <select id="id_service" name="id_service">
              <option value="">Consultation générale</option>
              @foreach ($veterinaire->services as $service)
                <option value="{{ $service->id_service }}">{{ $service->titre_service }} — {{ number_format($service->prix, 0, ',', ' ') }} FCFA</option>
              @endforeach
            </select>
          </div>
        @endif

        <div class="form-field">
          <label for="sujet">Sujet</label>
          <input type="text" id="sujet" name="sujet" value="{{ old('sujet') }}" required placeholder="Ex. Suspicion de maladie sur un bovin">
        </div>
        <div class="form-field">
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
        </div>
        <div class="form-field">
          <label for="date_prevue">Date et heure souhaitées</label>
          <input type="datetime-local" id="date_prevue" name="date_prevue" value="{{ old('date_prevue') }}" required>
        </div>

        <button type="submit" class="btn btn-primary auth-submit">Envoyer la demande</button>
      </form>
    </div>
  </div>
</section>
@endsection
