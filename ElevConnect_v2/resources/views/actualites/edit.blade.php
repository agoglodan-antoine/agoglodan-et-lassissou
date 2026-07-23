@extends('layouts.monEspace')

@section('title', 'Modifier l\'actualité — ElevConnect')

@push('styles')
   @vite(['resources/css/auth.css'])
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="auth-card" style="max-width:640px;">
      <x-back-link :href="route('actualites.show', $actualite)" label="Retour à l'actualité" />
      <h1>Modifier l'actualité</h1>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">
          <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('mon-espace.actualites.update', $actualite) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="form-field">
          <label for="titre">Titre</label>
          <input type="text" id="titre" name="titre" value="{{ old('titre', $actualite->titre) }}" required>
        </div>
        <div class="form-field">
          <label for="contenu">Contenu</label>
          <textarea id="contenu" name="contenu" rows="8" required>{{ old('contenu', $actualite->contenu) }}</textarea>
        </div>

        @if ($actualite->medias->isNotEmpty())
          <div class="form-field">
            <label>Images actuelles</label>
            <div style="display:flex;flex-wrap:wrap;gap:12px;">
              @foreach ($actualite->medias as $media)
                <label style="text-align:center;font-size:0.78rem;color:var(--ink-soft);cursor:pointer;">
                  <img src="{{ asset('storage/'.$media->chemin_fichier) }}" alt="" style="width:96px;height:72px;object-fit:cover;border-radius:var(--radius-sm);display:block;margin-bottom:4px;background:var(--sand);">
                  <input type="checkbox" name="supprimer_medias[]" value="{{ $media->id_media }}">
                  Supprimer
                </label>
              @endforeach
            </div>
          </div>
        @endif

        <div class="form-field">
          <label for="medias">Ajouter des images (facultatif, plusieurs possibles)</label>
          <input type="file" id="medias" name="medias[]" accept="image/*" data-max-mb="4" multiple>
        </div>

        <button type="submit" class="btn btn-primary auth-submit">Enregistrer</button>
      </form>
    </div>
  </div>
</section>
@endsection
