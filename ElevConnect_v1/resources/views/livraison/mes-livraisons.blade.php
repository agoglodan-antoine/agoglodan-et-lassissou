@extends('layouts.mon-espace')

@section('title', 'Mes livraisons — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Mes livraisons</h1>
        <p>Suivi de vos livraisons acceptées, de la prise en charge à la remise.</p>
      </div>
      <a href="{{ route('mon-espace.livraisons.proposees') }}" class="btn btn-ghost-dark">Voir les livraisons proposées</a>
    </div>


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
                <td style="font-size:0.82rem;">{{ $livraison->adresse_fournisseur }} → {{ $livraison->adresse_client }}</td>
                <td>{{ number_format($livraison->montant_net_livraison, 0, ',', ' ') }} FCFA</td>
                <td><span class="status-pill {{ $livraison->statut === 'terminee' ? 'visible' : ($livraison->statut === 'rejetee' ? 'rejetee' : 'en_attente') }}">{{ str_replace('_', ' ', $livraison->statut) }}</span></td>
                <td>
                  <div class="dash-actions">
                    <a href="{{ route('mon-espace.livraisons.show', $livraison) }}" class="btn btn-ghost-dark btn-sm">Voir</a>
                    @if ($livraison->statut === 'prise_en_charge')
                      <form method="POST" action="{{ route('mon-espace.livraisons.demarrer', $livraison) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Démarrer la course</button>
                      </form>
                    @endif
                    @if ($livraison->statut === 'en_cours')
                      <form method="POST" action="{{ route('mon-espace.livraisons.livrer', $livraison) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Marquer comme remise</button>
                      </form>
                    @endif
                    @if ($livraison->statut === 'terminee')
                      <span class="form-hint">Réception confirmée par l'acheteur ✓</span>
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
