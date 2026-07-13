@extends('layouts.app')

@section('title', 'Commandes reçues — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Commandes reçues</h1>
        <p>Traitez les commandes payées sur vos annonces, jusqu'à leur validation pour livraison.</p>
      </div>
    </div>

    @if (session('status'))
      <div class="dash-card" style="background:var(--pasture-soft);border:none;color:var(--pasture-dark);font-weight:600;">
        {{ session('status') }}
      </div>
    @endif
    @if ($errors->any())
      <div class="dash-card" style="background:rgba(189,74,30,0.08);border:none;color:var(--clay-dark);font-weight:600;">
        {{ $errors->first() }}
      </div>
    @endif

    @if ($commandes->isEmpty())
      <div class="empty-state">Aucune commande à traiter pour le moment.</div>
    @else
      @foreach ($commandes as $commande)
        <div class="moderation-card">
          <div style="flex:1;">
            <span class="status-pill {{ $commande->statut === 'validee' ? 'visible' : 'en_attente' }}">{{ str_replace('_', ' ', $commande->statut) }}</span>
            <h4 style="margin:8px 0 4px;">{{ $commande->annonce->titre }} — {{ $commande->quantite }} unité(s)</h4>
            <div style="font-size:0.85rem;color:var(--ink-soft);margin-bottom:14px;">
              Acheteur : {{ $commande->acheteur->nom }} {{ $commande->acheteur->prenom }} —
              Montant net : {{ number_format($commande->montant_net_commande, 0, ',', ' ') }} FCFA —
              {{ $commande->date_commande->format('d/m/Y à H:i') }}
            </div>

            <div class="dash-actions">
              @if ($commande->statut === 'payee')
                <form method="POST" action="{{ route('commandes-fournisseur.prendre-en-charge', $commande) }}">
                  @csrf
                  <button type="submit" class="btn btn-primary btn-sm">Prendre en charge</button>
                </form>
              @endif

              @if ($commande->statut === 'en_cours_de_traitement')
                <form method="POST" action="{{ route('commandes-fournisseur.valider', $commande) }}">
                  @csrf
                  <button type="submit" class="btn btn-primary btn-sm">Valider la commande</button>
                </form>
              @endif

              @if (in_array($commande->statut, ['payee', 'en_cours_de_traitement']))
                <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('refuse-{{ $commande->id_commande }}').classList.toggle('active-form')">Refuser</button>
              @endif
            </div>

            @if (in_array($commande->statut, ['payee', 'en_cours_de_traitement']))
              <form method="POST" action="{{ route('commandes-fournisseur.refuser', $commande) }}" class="moderation-reject-form" id="refuse-{{ $commande->id_commande }}" style="display:none;">
                @csrf
                <input type="text" name="motif_de_rejet" placeholder="Motif du refus (obligatoire)" required>
                <button type="submit" class="btn btn-danger btn-sm">Confirmer le refus</button>
              </form>
            @endif

            @if ($commande->statut === 'validee')
              <p class="form-hint" style="margin-top:8px;">
                Commande validée — en attente d'affectation à un livreur (module Livraison, Phase 4).
              </p>
            @endif
          </div>
        </div>
      @endforeach

      {{ $commandes->links() }}
    @endif
  </div>
</section>
@endsection

@push('scripts')
<script>
  // Affiche le mini-formulaire de refus au clic sur "Refuser".
  document.querySelectorAll('[id^="refuse-"]').forEach(form => {
    form.classList.add('js-toggle-target');
  });
  document.addEventListener('click', function(e){
    if (e.target.matches('.btn-danger[onclick]')) return; // géré inline ci-dessus
  });
  document.querySelectorAll('.moderation-reject-form').forEach(f => {
    // rétablit l'affichage flex quand la classe active-form est ajoutée
    const observer = new MutationObserver(() => {
      f.style.display = f.classList.contains('active-form') ? 'flex' : 'none';
    });
    observer.observe(f, { attributes: true, attributeFilter: ['class'] });
  });
</script>
@endpush
