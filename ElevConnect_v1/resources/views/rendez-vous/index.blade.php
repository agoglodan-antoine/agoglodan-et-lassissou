@extends('layouts.mon-espace')

@section('title', 'Mes rendez-vous — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Mes rendez-vous</h1>
        <p>Vos demandes de consultation auprès des vétérinaires.</p>
      </div>
      <a href="{{ route('veterinaires.index') }}" class="btn btn-primary">Trouver un vétérinaire</a>
    </div>


    <div class="dash-card" style="padding:0;">
      @if ($rdvs->isEmpty())
        <div class="empty-state">Vous n'avez pas encore de rendez-vous.</div>
      @else
        <div class="table-responsive">
<table class="dash-table">
          <thead>
            <tr><th>Vétérinaire</th><th>Sujet</th><th>Date</th><th>Statut</th><th></th></tr>
          </thead>
          <tbody>
            @foreach ($rdvs as $rdv)
              <tr>
                <td>Dr {{ $rdv->veterinaire->utilisateur->nom }} {{ $rdv->veterinaire->utilisateur->prenom }}</td>
                <td>{{ $rdv->sujet }}</td>
                <td>{{ $rdv->date_prevue->format('d/m/Y à H:i') }}</td>
                <td><span class="status-pill {{ in_array($rdv->statut, ['confirme','realise']) ? 'visible' : (in_array($rdv->statut, ['annule','refuse']) ? 'rejetee' : 'en_attente') }}">{{ str_replace('_', ' ', $rdv->statut) }}</span></td>
                <td>
                  <div class="dash-actions">
                    <a href="{{ route('mon-espace.rendez-vous.show', $rdv) }}" class="btn btn-ghost-dark btn-sm">Voir</a>
                    @if (in_array($rdv->statut, ['en_attente', 'confirme']))
                      <form method="POST" action="{{ route('mon-espace.rendez-vous.annuler', $rdv) }}" onsubmit="return confirm('Annuler ce rendez-vous ?');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Annuler</button>
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
