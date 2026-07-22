@extends('layouts.mon-espace')

@section('title', 'Modifier l\'actualité — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <a href="{{ route('actualites.show', $actualite) }}" class="me-back">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Retour à l'actualité
    </a>
    <div class="auth-card" style="max-width:640px;">
      <h1>Modifier l'actualité</h1>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('mon-espace.actualites.update', $actualite) }}">
        @csrf @method('PUT')
        <div class="form-field">
          <label for="titre">Titre</label>
          <input type="text" id="titre" name="titre" value="{{ old('titre', $actualite->titre) }}" required>
        </div>
        <div class="form-field">
          <label for="contenu">Contenu</label>
          <textarea id="contenu" name="contenu" rows="8" required>{{ old('contenu', $actualite->contenu) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary auth-submit">Enregistrer</button>
      </form>
    </div>
  </div>
</section>
@endsection
