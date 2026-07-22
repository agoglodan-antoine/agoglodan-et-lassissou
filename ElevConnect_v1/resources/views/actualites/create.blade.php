@extends('layouts.mon-espace')

@section('title', 'Publier une actualité — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <a href="{{ route('actualites.index') }}" class="me-back">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Retour aux actualités
    </a>
    <div class="auth-card" style="max-width:640px;">
      <h1>Publier une actualité</h1>
      <p class="sub">Partagez un conseil, une actualité de votre exploitation ou de la filière.</p>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('mon-espace.actualites.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-field">
          <label for="titre">Titre</label>
          <input type="text" id="titre" name="titre" value="{{ old('titre') }}" required>
        </div>
        <div class="form-field">
          <label for="contenu">Contenu</label>
          <textarea id="contenu" name="contenu" rows="8" required>{{ old('contenu') }}</textarea>
        </div>
        <div class="form-field">
          <label for="medias">Images (facultatif, plusieurs possibles)</label>
          <input type="file" id="medias" name="medias[]" accept="image/*" multiple>
        </div>

        <button type="submit" class="btn btn-primary auth-submit">Publier</button>
      </form>
    </div>
  </div>
</section>
@endsection
