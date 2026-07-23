@extends('layouts.monEspace')

@section('title', 'Historiques livraison — ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Historiques livraison</h1>
        <p>Suivi de vos livraisons acceptées, de la prise en charge à la remise.</p>
      </div>
      <a href="{{ route('mon-espace.livraison.proposees') }}" class="btn btn-ghost-dark">Voir les livraisons proposées</a>
    </div>

    @if (session('status'))
      <div class="dash-card" style="background:var(--pasture-soft);border:none;color:var(--pasture-dark);font-weight:600;">
        {{ session('status') }}
      </div>
    @endif

    <div class="dash-card" style="padding:0;">
      @if ($livraisons->isEmpty())
        <div class="empty-state">Vous n'avez aucune livraison en cours.</div>
      @else
        <div class="table-responsive">
<table class="dash-table">
          <thead>
            <tr>
              <th>Commande</th>
              <th>Trajet</th>
              <th>Frais nets</th>
              <th>Statut</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($livraisons as $livraison)
              <tr>
                <td><b>{{ $livraison->commande->annonce->titre }}</b></td>
                <td style="font-size:0.82rem;">{{ $livraison->adresse_fournisseur }} <i class="fa-solid fa-arrow-right"></i> {{ $livraison->adresse_client }}</td>
                <td>{{ number_format($livraison->montant_net_livraison, 0, ',', ' ') }} FCFA</td>
                <td><x-status-pill :status="$livraison->statut" /></td>
                <td>
                  <div class="dash-actions">
                    <a href="{{ route('mon-espace.livraison.show', $livraison) }}" class="btn btn-ghost-dark btn-sm">Voir</a>
                    @if ($livraison->statut === 'prise_en_charge')
                      <form method="POST" action="{{ route('mon-espace.livraison.demarrer', $livraison) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Démarrer la course</button>
                      </form>
                    @endif
                    @if ($livraison->statut === 'en_cours' && $livraison->commande->statut !== 'livree')
                      <form method="POST" action="{{ route('mon-espace.livraison.livrer', $livraison) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Livraison terminée</button>
                      </form>
                    @endif
                    @if ($livraison->statut === 'en_cours' && $livraison->commande->statut === 'livree')
                      <span class="form-hint">Code QR à montrer à l'acheteur</span>
                    @endif
                    @if ($livraison->statut === 'terminee')
                      <span class="form-hint">Réception confirmée par l'acheteur <i class="fa-solid fa-check"></i></span>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
</div>
      @endif
    </div>

    {{ $livraisons->links() }}
  </div>
</section>
@endsection
