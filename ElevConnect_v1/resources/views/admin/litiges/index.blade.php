@extends('layouts.mon-espace')

@section('title', 'Litiges — Administration ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">

    <div class="dash-head">
      <div>
        <h1>Litiges</h1>
        <p>Commandes signalées par l'acheteur après livraison.</p>
      </div>
    </div>


    @if ($litiges->isEmpty())
      <div class="empty-state">Aucun litige ouvert. 🎉</div>
    @else
      @foreach ($litiges as $commande)
        <div class="moderation-card">
          <div style="flex:1;">
            <h4 style="margin-bottom:6px;">Commande #{{ $commande->id_commande }} — {{ $commande->annonce->titre }}</h4>
            <div style="font-size:0.85rem;color:var(--ink-soft);margin-bottom:10px;">
              Acheteur : {{ $commande->acheteur->nom }} {{ $commande->acheteur->prenom }} —
              Fournisseur : {{ $commande->annonce->auteur->nom }} {{ $commande->annonce->auteur->prenom }} —
              Montant : {{ number_format($commande->montant_net_commande, 0, ',', ' ') }} FCFA
            </div>
            @if ($commande->description)
              <p style="font-size:0.88rem;color:var(--ink-soft);margin-bottom:14px;"><b>Motif signalé :</b> {{ $commande->description }}</p>
            @endif

            <div class="dash-actions">
              <form method="POST" action="{{ route('admin.litiges.faveur-acheteur', $commande) }}" onsubmit="return confirm('Trancher en faveur de l\'acheteur (remboursement) ?');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Trancher en faveur de l'acheteur (remboursement)</button>
              </form>
              <form method="POST" action="{{ route('admin.litiges.faveur-fournisseur', $commande) }}" onsubmit="return confirm('Trancher en faveur du fournisseur (versements) ?');">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">Trancher en faveur du fournisseur (versements)</button>
              </form>
            </div>
          </div>
        </div>
      @endforeach

      {{ $litiges->links() }}
    @endif
  </div>
</section>
@endsection
