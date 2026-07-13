@extends('layouts.app')

@section('title', 'Mes commandes — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Mes commandes</h1>
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
                <td><span class="status-pill {{ in_array($commande->statut, ['confirmee','validee']) ? 'visible' : ($commande->statut === 'annulee' || $commande->statut === 'refusee' ? 'rejetee' : 'en_attente') }}">{{ str_replace('_', ' ', $commande->statut) }}</span></td>
                <td>{{ $commande->date_commande->format('d/m/Y') }}</td>
                <td>
                  <div class="dash-actions">
                    @if ($commande->statut === 'en_attente')
                      <a href="{{ route('paiement.show', $commande) }}" class="btn btn-primary btn-sm">Payer</a>
                    @endif
                    <a href="{{ route('commandes.show', $commande) }}" class="btn btn-ghost-dark btn-sm">Détails</a>
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
