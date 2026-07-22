@extends('layouts.app')

@section('title', 'Commander — '.$annonce->titre)

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="auth-card" style="max-width:640px;">
      <x-back-link :href="route('catalogue.show', $annonce)" label="Retour à l'annonce" />
      <h1>Commander</h1>
      <p class="sub">{{ $annonce->titre }} — {{ number_format($annonce->prix_unitaire, 0, ',', ' ') }} FCFA / unité</p>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('commandes.store', $annonce) }}" id="orderForm">
        @csrf
        <div class="form-field">
          <label for="quantite">Quantité (disponible : {{ $annonce->quantite }})</label>
          <input type="number" id="quantite" name="quantite" min="1" max="{{ $annonce->quantite }}" value="{{ old('quantite', 1) }}" required>
        </div>

        <div class="form-field">
          <label>Mode de retrait</label>
          <div class="role-picker" style="grid-template-columns:1fr 1fr;">
            <div style="position:relative;">
              <input type="radio" name="mode_reception" id="mode-retrait" value="retrait_direct" checked>
              <label for="mode-retrait">Retrait direct<br><span style="font-weight:400;font-size:0.72rem;">auprès du fournisseur</span></label>
            </div>
            <div style="position:relative;">
              <input type="radio" name="mode_reception" id="mode-livreur" value="livreur" {{ $livreurs->isEmpty() ? 'disabled' : '' }}>
              <label for="mode-livreur">Faire livrer<br><span style="font-weight:400;font-size:0.72rem;">par un livreur</span></label>
            </div>
          </div>
        </div>

        <div class="form-field" id="livreurField" style="display:none;">
          <label>Choisir un livreur</label>
          @if ($livreurs->isEmpty())
            <p class="form-hint">Aucun livreur n'est actuellement disponible près du fournisseur — seul le retrait direct est possible.</p>
          @else
            <input type="hidden" id="id_livreur" name="id_livreur" value="">
            <button type="button" class="btn btn-ghost-dark" id="openLivreurModal" style="justify-content:flex-start;">
              <i class="fa-solid fa-truck"></i>&nbsp; <span id="livreurChoisiLabel">Sélectionner un livreur…</span>
            </button>
            <span class="form-hint">Triés par proximité avec le fournisseur. Le livreur choisi devra accepter votre livraison ; en cas de refus, elle sera automatiquement proposée à un autre.</span>
          @endif
        </div>

        @unless ($livreurs->isEmpty())
          <div class="qr-modal" id="livreurModal" style="display:none;">
            <div class="qr-modal-inner" style="max-width:480px;">
              <div class="qr-modal-head">
                <span>Choisir un livreur</span>
                <button type="button" id="closeLivreurModal" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <input
                type="text"
                id="livreurSearchInput"
                placeholder="Rechercher un livreur par nom…"
                style="width:100%;padding:0.6em 1em;border-radius:var(--radius-sm);border:1.5px solid var(--line);margin-bottom:14px;"
              >
              <div id="livreurResultsList" class="livreur-results"></div>
            </div>
          </div>
        @endunless

        <div class="dash-card" style="background:var(--sand);box-shadow:none;padding:20px;margin-top:20px;">
          <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span>Montant brut</span><b id="montantBrut">—</b>
          </div>
          <div style="display:flex;justify-content:space-between;margin-bottom:8px;color:var(--pasture-dark);">
            <span>Réduction applicable</span><b id="reduction">—</b>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:1.15rem;border-top:1px solid var(--line);padding-top:10px;">
            <span>Total à payer</span><b id="montantNet">—</b>
          </div>
        </div>

        <p class="form-hint" style="margin-top:16px;">
          Le paiement est réglé en ligne sur l'écran suivant. Les fonds resteront détenus
          en séquestre par ElevConnect jusqu'à la confirmation de réception (par vous-même,
          en main propre, ou après scan du code QR si un livreur intervient).
        </p>

        <button type="submit" class="btn btn-primary auth-submit" style="margin-top:8px;">Passer au paiement</button>
      </form>
    </div>
  </div>
</section>
@endsection

@php
  $livreursProchesJson = $livreurs->map(fn ($l) => [
      'id' => $l->id_utilisateur,
      'nom' => $l->nom,
      'prenom' => $l->prenom,
      'distance_km' => round($l->distance_km, 1),
      'moyen_transport' => $l->moyen_transport,
  ]);
@endphp

@push('scripts')
<script>
(function(){
  const prixUnitaire = {{ (float) $annonce->prix_unitaire }};
  const tranches = @json($tranchesReduction);

  const qteInput = document.getElementById('quantite');
  const brutEl = document.getElementById('montantBrut');
  const reducEl = document.getElementById('reduction');
  const netEl = document.getElementById('montantNet');

  function formatFcfa(n){
    return Math.round(n).toLocaleString('fr-FR') + ' FCFA';
  }

  function recompute(){
    const qte = parseInt(qteInput.value || '0', 10);
    const brut = prixUnitaire * qte;
    const tranche = tranches.find(t => qte >= t.min && qte <= t.max);
    const pct = tranche ? tranche.pct : 0;
    const reduction = brut * (pct / 100);

    brutEl.textContent = formatFcfa(brut);
    reducEl.textContent = pct > 0 ? '- ' + formatFcfa(reduction) + ' (' + pct + '%)' : 'Aucune';
    netEl.textContent = formatFcfa(brut - reduction);
  }

  qteInput.addEventListener('input', recompute);
  recompute();

  // Affiche le choix du livreur uniquement si "Faire livrer" est sélectionné.
  const livreurField = document.getElementById('livreurField');
  const radios = document.querySelectorAll('input[name="mode_reception"]');
  function toggleLivreurField(){
    const mode = document.querySelector('input[name="mode_reception"]:checked').value;
    livreurField.style.display = mode === 'livreur' ? 'flex' : 'none';
    livreurField.style.flexDirection = 'column';
  }
  radios.forEach(r => r.addEventListener('change', toggleLivreurField));
  toggleLivreurField();

  // Fenêtre de choix du livreur : petite liste des plus proches +
  // recherche AJAX par nom (débouncée) plutôt qu'un simple <select>.
  const openBtn = document.getElementById('openLivreurModal');
  if (openBtn) {
    const modal = document.getElementById('livreurModal');
    const closeBtn = document.getElementById('closeLivreurModal');
    const searchInput = document.getElementById('livreurSearchInput');
    const resultsList = document.getElementById('livreurResultsList');
    const idInput = document.getElementById('id_livreur');
    const choisiLabel = document.getElementById('livreurChoisiLabel');
    const rechercheUrl = '{{ route('commandes.livreurs.rechercher', $annonce) }}';
    const livreursProches = @json($livreursProchesJson);

    let debounceTimer = null;

    function renderResults(livreurs) {
      resultsList.innerHTML = '';

      if (livreurs.length === 0) {
        resultsList.innerHTML = '<p class="livreur-results-empty">Aucun livreur trouvé.</p>';
        return;
      }

      livreurs.forEach(function (l) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'livreur-option';

        const infosParts = [];
        if (typeof l.distance_km === 'number') infosParts.push(l.distance_km + ' km');
        if (l.moyen_transport) infosParts.push(l.moyen_transport);

        btn.innerHTML = '<span><b></b><span></span></span><i class="fa-solid fa-chevron-right"></i>';
        btn.querySelector('b').textContent = l.nom + ' ' + l.prenom;
        btn.querySelector('span span').textContent = infosParts.join(' · ');

        btn.addEventListener('click', function () {
          idInput.value = l.id;
          choisiLabel.textContent = l.nom + ' ' + l.prenom;
          modal.style.display = 'none';
        });

        resultsList.appendChild(btn);
      });
    }

    function rechercher(q) {
      fetch(rechercheUrl + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) { renderResults(data.livreurs || []); })
        .catch(function () { renderResults([]); });
    }

    openBtn.addEventListener('click', function () {
      modal.style.display = 'flex';
      searchInput.value = '';
      renderResults(livreursProches);
      searchInput.focus();
    });

    closeBtn.addEventListener('click', function () {
      modal.style.display = 'none';
    });

    modal.addEventListener('click', function (e) {
      if (e.target === modal) modal.style.display = 'none';
    });

    searchInput.addEventListener('input', function () {
      const q = searchInput.value.trim();
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function () {
        if (q.length === 0) {
          renderResults(livreursProches);
        } else {
          rechercher(q);
        }
      }, 300);
    });
  }
})();
</script>
@endpush