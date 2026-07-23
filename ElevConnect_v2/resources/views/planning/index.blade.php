@extends('layouts.monEspace')

@section('title', 'Mon planning — ElevConnect')

@push('styles')
   @vite(['resources/css/auth.css'])
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Mon planning de disponibilité</h1>
        <p>Déclarez vos créneaux d'indisponibilité : les livraisons ne vous seront pas proposées pendant ces périodes.</p>
      </div>
    </div>

    @if (session('status'))
      <div class="dash-card" style="background:var(--pasture-soft);box-shadow:none;color:var(--pasture-dark);font-weight:600;">
        {{ session('status') }}
      </div>
    @endif

    <div class="auth-card" style="max-width:640px;margin:0 0 32px;">
      <h3 style="font-size:1.05rem;margin-bottom:14px;">Ajouter un créneau d'indisponibilité</h3>
      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">
          <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      <form method="POST" action="{{ route('mon-espace.planning.store') }}">
        @csrf
        <div class="form-grid">
          <div class="form-field">
            <label>Du</label>
            <input type="datetime-local" name="date_debut" required>
          </div>
          <div class="form-field">
            <label>Au</label>
            <input type="datetime-local" name="date_fin" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Ajouter</button>
      </form>
    </div>

    <div class="dash-card">
      <h3 style="font-size:1.05rem;margin-bottom:14px;">Créneaux déclarés</h3>
      @if ($creneaux->isEmpty())
        <p class="form-hint">Aucun créneau d'indisponibilité déclaré — vous êtes considéré disponible en permanence.</p>
      @else
        <div class="table-responsive">
          <table class="dash-table">
            <thead>
              <tr><th>Du</th><th>Au</th><th>Statut</th><th></th></tr>
            </thead>
            <tbody>
              @foreach ($creneaux as $creneau)
                <tr>
                  <td>{{ $creneau->date_debut->format('d/m/Y H:i') }}</td>
                  <td>{{ $creneau->date_fin->format('d/m/Y H:i') }}</td>
                  <td>
                    <x-status-pill
                      :force="$creneau->date_fin->isPast() ? 'rejetee' : 'en_attente'"
                      :label="$creneau->date_fin->isPast() ? 'Passé' : 'Indisponible'"
                    />
                  </td>
                  <td>
                    <form method="POST" action="{{ route('mon-espace.planning.destroy', $creneau) }}" onsubmit="return confirm('Supprimer ce créneau ?');">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</section>
@endsection
