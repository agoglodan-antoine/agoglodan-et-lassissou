@extends('layouts.monEspace')

@section('title', 'Mes achats — ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Mes achats</h1>
        <p>Suivez l'état de vos commandes, du paiement à la livraison.</p>
      </div>
    </div>

    @if (session('status'))
      <div class="dash-card" style="background:var(--pasture-soft);border:none;color:var(--pasture-dark);font-weight:600;">
        {{ session('status') }}
      </div>
    @endif

    <div class="dash-card" style="padding:0;">
      @if ($commandes->isEmpty())
        <div class="empty-state">
          Vous n'avez pas encore passé de commande.
          <br><a href="{{ route('catalogue.index') }}" class="btn btn-ghost-dark" style="margin-top:16px;display:inline-flex;">Parcourir les annonces</a>
        </div>
      @else
        <div class="table-responsive">
<table class="dash-table">
          <thead>
            <tr>
              <th>Commande</th>
              <th>Quantité</th>
              <th>Montant net</th>
              <th>Statut</th>
              <th>Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($commandes as $commande)
              <tr>
                <td><b>{{ $commande->annonce->titre }}</b></td>
                <td>{{ $commande->quantite }}</td>
                <td>{{ number_format($commande->montant_net_commande, 0, ',', ' ') }} FCFA</td>
                <td><x-status-pill :status="$commande->statut" /></td>
                <td>{{ $commande->date_commande->format('d/m/Y') }}</td>
                <td>
                  <div class="dash-actions">
                    @if ($commande->statut === 'en_attente')
                      <a href="{{ route('paiement.show', $commande) }}" class="btn btn-primary btn-sm">Payer</a>
                    @endif
                    <a href="{{ route('mon-espace.commandes.show', $commande) }}" class="btn btn-ghost-dark btn-sm">Détails</a>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
</div>
      @endif
    </div>

    {{ $commandes->links() }}
  </div>
</section>
@endsection
