@extends('layouts.monEspace')

@section('title', 'Commande '.$commande->code_authenticite.' — ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:700px;">
    <div class="dash-card">
      <x-back-link :href="route('mon-espace.commandes-fournisseur.index')" label="Retour aux commandes reçues" />

      @if (session('status'))
        <div class="dash-card" style="background:var(--pasture-soft);box-shadow:none;color:var(--pasture-dark);font-weight:600;">
          {{ session('status') }}
        </div>
      @endif
      @if ($errors->any())
        <div class="dash-card" style="background:rgba(189,74,30,0.08);box-shadow:none;color:var(--clay-dark);font-weight:600;">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="dash-head" style="margin-bottom:20px;">
        <div>
          <h1 style="font-size:1.5rem;">Commande {{ $commande->code_authenticite }}</h1>
          <p>{{ $commande->annonce->titre }} — {{ $commande->date_commande->format('d/m/Y à H:i') }}</p>
        </div>
        <x-status-pill :status="$commande->statut" />
      </div>

      <div class="form-grid" style="margin-bottom:24px;">
        <div class="form-field">
          <label>Acheteur</label>
          <p><b>{{ $commande->acheteur->nom }} {{ $commande->acheteur->prenom }}</b></p>
        </div>
        <div class="form-field">
          <label>Quantité</label>
          <p><b>{{ $commande->quantite }}</b></p>
        </div>
        <div class="form-field">
          <label>Montant net</label>
          <p><b>{{ number_format($commande->montant_net_commande, 0, ',', ' ') }} FCFA</b></p>
        </div>
        <div class="form-field">
          <label>Mode de retrait</label>
          <p><b>{{ $commande->estRetraitDirect() ? 'Retrait direct' : 'Livraison' }}</b></p>
        </div>
      </div>

      @if ($commande->livraison)
        <div class="dash-card" style="background:var(--sand);box-shadow:none;margin-bottom:20px;">
          <b>Suivi de la livraison :</b>
          <x-status-pill :status="$commande->livraison->statut" />
        </div>
      @endif

      <div class="dash-actions">
        @if ($commande->statut === 'payee')
          <form method="POST" action="{{ route('mon-espace.commandes-fournisseur.prendre-en-charge', $commande) }}">
            @csrf
            <button type="submit" class="btn btn-primary">Prendre en charge</button>
          </form>
        @endif

        @if ($commande->statut === 'en_cours_de_traitement')
          <form method="POST" action="{{ route('mon-espace.commandes-fournisseur.valider', $commande) }}">
            @csrf
            <button type="submit" class="btn btn-primary">Valider la commande</button>
          </form>
        @endif

        @if (in_array($commande->statut, ['payee', 'en_cours_de_traitement']))
          <button type="button" class="btn btn-danger" onclick="document.getElementById('refuse-form').classList.toggle('active-form')">Refuser</button>
        @endif
      </div>

      @if (in_array($commande->statut, ['payee', 'en_cours_de_traitement']))
        <form method="POST" action="{{ route('mon-espace.commandes-fournisseur.refuser', $commande) }}" class="moderation-reject-form" id="refuse-form" style="display:none;margin-top:14px;">
          @csrf
          <input type="text" name="motif_de_rejet" placeholder="Motif du refus (obligatoire)" required>
          <button type="submit" class="btn btn-danger btn-sm">Confirmer le refus</button>
        </form>
      @endif

      @if ($commande->statut === 'validee')
        <p class="form-hint" style="margin-top:14px;">Commande validée.</p>
      @endif
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  const refuseForm = document.getElementById('refuse-form');
  if (refuseForm) {
    const observer = new MutationObserver(() => {
      refuseForm.style.display = refuseForm.classList.contains('active-form') ? 'flex' : 'none';
    });
    observer.observe(refuseForm, { attributes: true, attributeFilter: ['class'] });
  }
</script>
@endpush
