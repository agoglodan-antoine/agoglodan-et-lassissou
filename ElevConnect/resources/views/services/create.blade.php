@extends('layouts.app')

@section('title', 'Nouveau service — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="auth-card" style="max-width:640px;">
      <h1>Nouveau service</h1>
      <p class="sub">Décrivez la prestation proposée aux éleveurs.</p>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('services.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-field">
          <label for="titre_service">Titre du service</label>
          <input type="text" id="titre_service" name="titre_service" value="{{ old('titre_service') }}" required placeholder="Ex. Vaccination antirabique">
        </div>
        <div class="form-field">
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
        </div>
        <div class="form-grid">
          <div class="form-field">
            <label for="prix">Prix (FCFA)</label>
            <input type="number" step="0.01" min="0" id="prix" name="prix" value="{{ old('prix') }}" required>
          </div>
          <div class="form-field">
            <label for="temps_traitement">Durée estimée (minutes)</label>
            <input type="number" min="5" id="temps_traitement" name="temps_traitement" value="{{ old('temps_traitement', 30) }}" required>
          </div>
          <div class="form-field full">
            <label for="photo_illustrative">Photo illustrative (facultatif)</label>
            <input type="file" id="photo_illustrative" name="photo_illustrative" accept="image/*">
          </div>
        </div>

        <button type="submit" class="btn btn-primary auth-submit">Publier le service</button>
      </form>
    </div>
  </div>
</section>
@endsection
