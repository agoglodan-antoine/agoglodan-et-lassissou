@extends('layouts.mon-espace')

@section('title', 'Rendez-vous reçus — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Rendez-vous reçus</h1>
        <p>Confirmez, refusez ou clôturez les demandes des éleveurs.</p>
      </div>
    </div>


    <div class="dash-card" style="padding:0;">
      @if ($rdvs->isEmpty())
        <div class="empty-state">Aucune demande de rendez-vous pour le moment.</div>
      @else
        <div class="table-responsive">
<table class="dash-table">
          <thead>
            <tr><th>Éleveur</th><th>Sujet</th><th>Date</th><th>Statut</th><th></th></tr>
          </thead>
          <tbody>
            @foreach ($rdvs as $rdv)
              <tr>
                <td>{{ $rdv->eleveur->utilisateur->nom }} {{ $rdv->eleveur->utilisateur->prenom }}</td>
                <td>{{ $rdv->sujet }}@if($rdv->service) <br><small>{{ $rdv->service->titre_service }}</small>@endif</td>
                <td>{{ $rdv->date_prevue->format('d/m/Y à H:i') }}</td>
                <td><span class="status-pill {{ in_array($rdv->statut, ['confirme','realise']) ? 'visible' : (in_array($rdv->statut, ['annule','refuse']) ? 'rejetee' : 'en_attente') }}">{{ str_replace('_', ' ', $rdv->statut) }}</span></td>
                <td>
                  <div class="dash-actions">
                    <a href="{{ route('mon-espace.rendez-vous.show', $rdv) }}" class="btn btn-ghost-dark btn-sm">Voir</a>
                    @if ($rdv->statut === 'en_attente')
                      <form method="POST" action="{{ route('mon-espace.rendez-vous-recus.confirmer', $rdv) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Confirmer</button>
                      </form>
                      <form method="POST" action="{{ route('mon-espace.rendez-vous-recus.refuser', $rdv) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Refuser</button>
                      </form>
                    @endif
                    @if ($rdv->statut === 'confirme')
                      <form method="POST" action="{{ route('mon-espace.rendez-vous-recus.realise', $rdv) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Marquer réalisé</button>
                      </form>
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

    {{ $rdvs->links() }}
  </div>
</section>
@endsection
