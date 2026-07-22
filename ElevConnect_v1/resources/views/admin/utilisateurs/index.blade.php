@extends('layouts.mon-espace')

@section('title', 'Utilisateurs — Administration ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">

    <div class="dash-head">
      <div>
        <h1>Utilisateurs</h1>
        <p>{{ $utilisateurs->total() }} compte(s) enregistré(s).</p>
      </div>
    </div>


    <form method="GET" action="{{ route('admin.utilisateurs.index') }}" class="filter-bar">
      <div class="form-field">
        <label>Rôle</label>
        <select name="role">
          <option value="">Tous</option>
          @foreach (['eleveur','acheteur','vendeur_provende','vendeur_accessoire','veterinaire','livreur','administrateur'] as $r)
            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $r)) }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-field">
        <label>Recherche</label>
        <input type="text" name="recherche" value="{{ request('recherche') }}" placeholder="Nom, prénom, email">
      </div>
      <button type="submit" class="btn btn-primary">Filtrer</button>
    </form>

    <div class="dash-card" style="padding:0;">
      <div class="table-responsive">
<table class="dash-table">
        <thead>
          <tr><th>Nom</th><th>Rôle</th><th>Email</th><th>Statut</th><th>Inscrit le</th><th></th></tr>
        </thead>
        <tbody>
          @foreach ($utilisateurs as $u)
            <tr>
              <td><b>{{ $u->nom }} {{ $u->prenom }}</b></td>
              <td>{{ ucfirst(str_replace('_',' ', $u->role)) }}</td>
              <td>{{ $u->email }}</td>
              <td><span class="status-pill {{ $u->statut === 'actif' ? 'visible' : 'rejetee' }}">{{ $u->statut }}</span></td>
              <td>{{ $u->date_inscription->format('d/m/Y') }}</td>
              <td>
                <div class="dash-actions">
                  <a href="{{ route('admin.utilisateurs.show', $u) }}" class="btn btn-ghost-dark btn-sm">Voir</a>
                  @if ($u->role !== 'administrateur')
                    @if ($u->statut === 'actif')
                      <form method="POST" action="{{ route('admin.utilisateurs.suspendre', $u) }}" onsubmit="return confirm('Suspendre ce compte ?');">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Suspendre</button>
                      </form>
                    @else
                      <form method="POST" action="{{ route('admin.utilisateurs.reactiver', $u) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Réactiver</button>
                      </form>
                    @endif
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
</div>
    </div>

    {{ $utilisateurs->links() }}
  </div>
</section>
@endsection
