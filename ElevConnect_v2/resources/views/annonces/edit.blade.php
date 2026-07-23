@extends('layouts.monEspace')

@section('title', 'Modifier mon annonce — ElevConnect')

@push('styles')
   @vite(['resources/css/auth.css'])
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="auth-card" style="max-width:720px;">
      <x-back-link :href="route('mon-espace.annonces.show', $annonce)" label="Retour au détail de l'annonce" />
      <h1>Modifier mon annonce</h1>
      <p class="sub">Toute modification repasse l'annonce en attente de validation par un administrateur.</p>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">
          <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('mon-espace.annonces.update', $annonce) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="form-grid">
          <div class="form-field full">
            <label for="titre">Titre de l'annonce</label>
            <input type="text" id="titre" name="titre" value="{{ old('titre', $annonce->titre) }}" required>
          </div>
          <div class="form-field full">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4">{{ old('description', $annonce->description) }}</textarea>
          </div>
          <div class="form-field">
            <label for="prix_unitaire">Prix unitaire (FCFA)</label>
            <input type="number" step="0.01" min="0" id="prix_unitaire" name="prix_unitaire" value="{{ old('prix_unitaire', $annonce->prix_unitaire) }}" required>
          </div>
          <div class="form-field">
            <label for="quantite">Quantité disponible</label>
            <input type="number" min="1" id="quantite" name="quantite" value="{{ old('quantite', $annonce->quantite) }}" required>
          </div>

          @if ($annonce->type_annonce === 'animal')
            <div class="form-field">
              <label for="poids">Poids (kg)</label>
              <input type="number" step="0.01" min="0" id="poids" name="poids" value="{{ old('poids', $annonce->poids) }}" required>
            </div>
            <div class="form-field">
              <label for="mois">Âge (mois)</label>
              <input type="number" min="0" id="mois" name="mois" value="{{ old('mois', $annonce->mois) }}" required>
            </div>
          @else
            <div class="form-field">
              <label for="unite_de_mesure">Unité de mesure</label>
              <select id="unite_de_mesure" name="unite_de_mesure" required>
                @foreach (['sac' => 'Sac', 'kg' => 'Kilogramme', 'l' => 'Litre', 'bassine' => 'Bassine', 'autre' => 'Autre'] as $val => $label)
                  <option value="{{ $val }}" {{ old('unite_de_mesure', $annonce->unite_de_mesure) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
          @endif

          <div class="form-field">
            <label for="etat">Disponibilité</label>
            <select id="etat" name="etat" required>
              <option value="disponible" {{ old('etat', $annonce->etat) === 'disponible' ? 'selected' : '' }}>Disponible</option>
              <option value="stock_epuise" {{ old('etat', $annonce->etat) === 'stock_epuise' ? 'selected' : '' }}>Stock épuisé</option>
            </select>
          </div>

          <div class="form-field">
            <label for="image_1">Remplacer la photo principale</label>
            @if ($annonce->image_1)
              <img src="{{ asset('storage/'.$annonce->image_1) }}" alt="Photo principale actuelle" style="width:120px;height:90px;object-fit:cover;border-radius:var(--radius-sm);margin-bottom:8px;background:var(--sand);">
            @endif
            <input type="file" id="image_1" name="image_1" accept="image/*" data-max-mb="4">
            <span class="form-hint">Laisser vide pour conserver la photo actuelle.</span>
          </div>
          <div class="form-field">
            <label for="image_2">Remplacer la photo secondaire</label>
            @if ($annonce->image_2)
              <img src="{{ asset('storage/'.$annonce->image_2) }}" alt="Photo secondaire actuelle" style="width:120px;height:90px;object-fit:cover;border-radius:var(--radius-sm);margin-bottom:8px;background:var(--sand);">
            @endif
            <input type="file" id="image_2" name="image_2" accept="image/*" data-max-mb="4">
          </div>
        </div>

        <h3 style="margin:28px 0 4px;font-size:1.1rem;">Réductions par quantité <span style="font-weight:400;color:var(--ink-soft);font-size:0.85rem;">(facultatif)</span></h3>
        <div id="reductionsWrap"></div>
        <button type="button" class="btn btn-ghost-dark btn-sm" id="addReduction" style="margin-bottom:24px;">+ Ajouter une tranche</button>

        <button type="submit" class="btn btn-primary auth-submit">Enregistrer les modifications</button>
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

  function addRow(values){
    values = values || {};
    const row = document.createElement('div');
    row.className = 'reduction-row';
    row.innerHTML = `
      <div class="form-field">
        <label>Quantité min.</label>
        <input type="number" min="1" name="reductions[${i}][quantite_min]" value="${values.quantite_min ?? ''}">
      </div>
      <div class="form-field">
        <label>Quantité max.</label>
        <input type="number" min="1" name="reductions[${i}][quantite_max]" value="${values.quantite_max ?? ''}">
      </div>
      <div class="form-field">
        <label>Réduction (%)</label>
        <input type="number" min="0" max="100" step="0.1" name="reductions[${i}][pourcentage_reduction]" value="${values.pourcentage_reduction ?? ''}">
      </div>
      <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa-solid fa-xmark"></i></button>
    `;
    row.querySelector('.remove-row').addEventListener('click', () => row.remove());
    wrap.appendChild(row);
    i++;
  }

  addBtn.addEventListener('click', () => addRow());

  const existing = @json($reductionsExistantes);
  existing.forEach(addRow);
})();
</script>
@endpush
