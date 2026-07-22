@extends('layouts.monEspace')

@section('title', 'Mes services — ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Mes services</h1>
        <p>
          Formule actuelle :
          <b>{{ $veterinaire->estPremium() ? 'Premium' : 'Basique' }}</b>
          @if (! $veterinaire->estPremium())
            ({{ $services->total() }}/{{ config('elevconnect.abonnement_veterinaire.basique.limite_services') }} services)
          @endif
          — <a href="{{ route('mon-espace.abonnement.show') }}">gérer mon abonnement</a>
        </p>
      </div>
      @if (! $limiteAtteinte)
        <a href="{{ route('mon-espace.services.create') }}" class="btn btn-primary">+ Nouveau service</a>
      @else
        <a href="{{ route('mon-espace.abonnement.show') }}" class="btn btn-primary">Passer en Premium pour en ajouter</a>
      @endif
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

    <div class="dash-card" style="padding:0;">
      @if ($services->isEmpty())
        <div class="empty-state">
          Vous n'avez encore publié aucun service.
          <br><a href="{{ route('mon-espace.services.create') }}" class="btn btn-ghost-dark" style="margin-top:16px;display:inline-flex;">Publier mon premier service</a>
        </div>
      @else
        <div class="table-responsive">
<table class="dash-table">
          <thead>
            <tr><th>Service</th><th>Prix</th><th>Durée</th><th>Statut</th><th></th></tr>
          </thead>
          <tbody>
            @foreach ($services as $service)
              <tr>
                <td><b>{{ $service->titre_service }}</b></td>
                <td>{{ number_format($service->prix, 0, ',', ' ') }} FCFA</td>
                <td>{{ $service->temps_traitement }} min</td>
                <td><x-status-pill :status="$service->statut_service" /></td>
                <td>
                  <div class="dash-actions">
                    <a href="{{ route('mon-espace.services.show', $service) }}" class="btn btn-ghost-dark btn-sm">Voir</a>
                    <a href="{{ route('mon-espace.services.edit', $service) }}" class="btn btn-ghost-dark btn-sm">Modifier</a>
                    <form method="POST" action="{{ route('mon-espace.services.destroy', $service) }}" onsubmit="return confirm('Supprimer ce service ?');">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
</div>
      @endif
    </div>

    {{ $services->links() }}
  </div>
</section>
@endsection
