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
          <label for="id_livreur">Choisir un livreur</label>
          @if ($livreurs->isEmpty())
            <p class="form-hint">Aucun livreur n'est actuellement disponible près du fournisseur — seul le retrait direct est possible.</p>
          @else
            <select id="id_livreur" name="id_livreur">
              @foreach ($livreurs as $livreur)
                <option value="{{ $livreur->id_utilisateur }}">
                  {{ $livreur->nom }} {{ $livreur->prenom }}
                  @isset($livreur->distance_km)
                    — {{ number_format($livreur->distance_km, 1) }} km
                  @endisset
                  @if ($livreur->moyen_transport)
                    ({{ $livreur->moyen_transport }})
                  @endif
                </option>
              @endforeach
            </select>
            <span class="form-hint">Triés par proximité avec le fournisseur. Le livreur choisi devra accepter votre livraison ; en cas de refus, elle sera automatiquement proposée à un autre.</span>
          @endif
        </div>

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

@push('scripts')
<script>
(function(){
  const prixUnitaire = {{ (float) $annonce->prix_unitaire }};
  const tranches = @json($reductions);

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
})();
</script>
@endpush
