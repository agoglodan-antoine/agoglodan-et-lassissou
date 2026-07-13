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
          en séquestre par ElevConnect jusqu'à la confirmation de réception (code QR).
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

  {{--
    IMPORTANT : @json() sépare ses arguments (valeur, options, profondeur) avec
    un simple explode(','), sans tenir compte des virgules imbriquées dans un
    tableau ou un appel de fonction. On précalcule donc le tableau ici, dans
    une variable PHP "plate" (sans virgule au niveau de l'expression passée à
    @json), pour éviter que les virgules du tableau ne cassent la compilation.
  --}}
  @php
    $tranches = $annonce->reductions->map(fn ($r) => [
        'min' => (int) $r->quantite_min,
        'max' => (int) $r->quantite_max,
        'pct' => (float) $r->pourcentage_reduction,
    ])->values();
  @endphp
  const tranches = @json($tranches);

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
})();
</script>
@endpush
