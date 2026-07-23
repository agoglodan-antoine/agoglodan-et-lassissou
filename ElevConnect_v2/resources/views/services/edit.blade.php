@extends('layouts.monEspace')

@section('title', 'Modifier le service — ElevConnect')

@push('styles')
   @vite(['resources/css/auth.css'])
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="auth-card" style="max-width:640px;">
      <x-back-link :href="route('mon-espace.services.show', $service)" label="Retour au détail du service" />
      <h1>Modifier le service</h1>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('mon-espace.services.update', $service) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="form-field">
          <label for="titre_service">Titre du service</label>
          <input type="text" id="titre_service" name="titre_service" value="{{ old('titre_service', $service->titre_service) }}" required>
        </div>
        <div class="form-field">
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="4">{{ old('description', $service->description) }}</textarea>
        </div>
        <div class="form-grid">
          <div class="form-field">
            <label for="prix">Prix (FCFA)</label>
            <input type="number" step="0.01" min="0" id="prix" name="prix" value="{{ old('prix', $service->prix) }}" required>
          </div>
          <div class="form-field">
            <label for="temps_traitement">Durée estimée (minutes)</label>
            <input type="number" min="5" id="temps_traitement" name="temps_traitement" value="{{ old('temps_traitement', $service->temps_traitement) }}" required>
          </div>
          <div class="form-field">
            <label for="statut_service">Disponibilité</label>
            <select id="statut_service" name="statut_service" required>
              <option value="disponible" {{ old('statut_service', $service->statut_service) === 'disponible' ? 'selected' : '' }}>Disponible</option>
              <option value="indisponible" {{ old('statut_service', $service->statut_service) === 'indisponible' ? 'selected' : '' }}>Indisponible</option>
            </select>
          </div>
          <div class="form-field full">
            <label for="photo_illustrative">Remplacer la photo</label>
            <input type="file" id="photo_illustrative" name="photo_illustrative" accept="image/*">
          </div>
        </div>

        <button type="submit" class="btn btn-primary auth-submit">Enregistrer</button>
      </form>
    </div>
  </div>
</section>
@endsection
