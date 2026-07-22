@extends('layouts.mon-espace')

@section('title', 'Publier une annonce — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <a href="{{ route('mon-espace.annonces.index') }}" class="me-back">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Retour à mes annonces
    </a>
    <div class="auth-card" style="max-width:720px;">
      <h1>Publier une annonce</h1>
      <p class="sub">
        @if ($type === 'animal')
          Votre annonce sera publiée en tant qu'<b>animal</b> et soumise à validation par un administrateur avant d'être visible.
        @else
          Votre annonce sera publiée en tant que <b>{{ $type }}</b> et soumise à validation par un administrateur avant d'être visible.
        @endif
      </p>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">
          <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('mon-espace.annonces.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-grid">
          <div class="form-field full">
            <label for="titre">Titre de l'annonce</label>
            <input type="text" id="titre" name="titre" value="{{ old('titre') }}" required>
          </div>
          <div class="form-field full">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
          </div>
          <div class="form-field">
            <label for="prix_unitaire">Prix unitaire (FCFA)</label>
            <input type="number" step="0.01" min="0" id="prix_unitaire" name="prix_unitaire" value="{{ old('prix_unitaire') }}" required>
          </div>
          <div class="form-field">
            <label for="quantite">Quantité disponible</label>
            <input type="number" min="1" id="quantite" name="quantite" value="{{ old('quantite', 1) }}" required>
          </div>

          @if ($type === 'animal')
            <div class="form-field">
              <label for="poids">Poids (kg)</label>
              <input type="number" step="0.01" min="0" id="poids" name="poids" value="{{ old('poids') }}" required>
            </div>
            <div class="form-field">
              <label for="mois">Âge (mois)</label>
              <input type="number" min="0" id="mois" name="mois" value="{{ old('mois') }}" required>
            </div>
          @else
            <div class="form-field">
              <label for="unite_de_mesure">Unité de mesure</label>
              <select id="unite_de_mesure" name="unite_de_mesure" required>
                <option value="sac">Sac</option>
                <option value="kg">Kilogramme</option>
                <option value="l">Litre</option>
                <option value="bassine">Bassine</option>
                <option value="autre">Autre</option>
              </select>
            </div>
          @endif

          <div class="form-field">
            <label for="image_1">Photo principale</label>
            <input type="file" id="image_1" name="image_1" accept="image/*" required>
          </div>
          <div class="form-field">
            <label for="image_2">Photo secondaire (facultatif)</label>
            <input type="file" id="image_2" name="image_2" accept="image/*">
          </div>
        </div>

        <h3 style="margin:28px 0 4px;font-size:1.1rem;">Réductions par quantité <span style="font-weight:400;color:var(--ink-soft);font-size:0.85rem;">(facultatif)</span></h3>
        <p class="form-hint" style="margin-bottom:16px;">Proposez un pourcentage de réduction selon la quantité commandée.</p>

        <div id="reductionsWrap"></div>
        <button type="button" class="btn btn-ghost-dark btn-sm" id="addReduction" style="margin-bottom:24px;">+ Ajouter une tranche</button>

        <button type="submit" class="btn btn-primary auth-submit">Soumettre l'annonce</button>
      </form>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
(function(){
  const wrap = document.getElementById('reductionsWrap');
  const addBtn = document.getElementById('addReduction');
  let i = 0;

  function addRow(){
    const row = document.createElement('div');
    row.className = 'reduction-row';
    row.innerHTML = `
      <div class="form-field">
        <label>Quantité min.</label>
        <input type="number" min="1" name="reductions[${i}][quantite_min]">
      </div>
      <div class="form-field">
        <label>Quantité max.</label>
        <input type="number" min="1" name="reductions[${i}][quantite_max]">
      </div>
      <div class="form-field">
        <label>Réduction (%)</label>
        <input type="number" min="0" max="100" step="0.1" name="reductions[${i}][pourcentage_reduction]">
      </div>
      <button type="button" class="btn btn-danger btn-sm remove-row">✕</button>
    `;
    row.querySelector('.remove-row').addEventListener('click', () => row.remove());
    wrap.appendChild(row);
    i++;
  }

  addBtn.addEventListener('click', addRow);
})();
</script>
@endpush
