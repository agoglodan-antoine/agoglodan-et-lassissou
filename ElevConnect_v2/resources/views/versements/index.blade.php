@extends('layouts.monEspace')

@section('title', 'Mes versements — ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Mes versements</h1>
        <p>Montants reversés par ElevConnect après confirmation de réception d'une commande (commission déduite).</p>
      </div>
    </div>

    <div class="dash-card">
      @if ($versements->isEmpty())
        <p class="form-hint">Aucun versement pour l'instant.</p>
      @else
        <div class="table-responsive">
          <table class="dash-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Commande</th>
                <th>Rôle</th>
                <th>Montant net</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($versements as $versement)
                <tr>
                  <td>{{ $versement->date_versement->format('d/m/Y H:i') }}</td>
                  <td>{{ $versement->commande->code_authenticite }} — {{ $versement->commande->annonce->titre ?? '—' }}</td>
                  <td>{{ $versement->type_beneficiaire === 'fournisseur' ? 'Fournisseur' : 'Livreur' }}</td>
                  <td><b>{{ number_format($versement->montant_verser, 0, ',', ' ') }} FCFA</b></td>
                  <td><x-status-pill :status="$versement->statut_versement" :force="$versement->statut_versement === 'reussi' ? 'visible' : 'rejetee'" /></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div style="margin-top:16px;">{{ $versements->links() }}</div>
      @endif
    </div>
  </div>
</section>
@endsection
