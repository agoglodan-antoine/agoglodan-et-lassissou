@extends('layouts.monEspace')

@section('title', $utilisateur->nom.' '.$utilisateur->prenom.' — Administration ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:700px;">
    @include('admin._nav')

    <div class="dash-card">
      <x-back-link :href="route('mon-espace.admin.utilisateurs.index')" label="Retour aux utilisateurs" />

      @if (session('status'))
        <div class="dash-card" style="background:var(--pasture-soft);box-shadow:none;color:var(--pasture-dark);font-weight:600;">
          {{ session('status') }}
        </div>
      @endif

      <div class="dash-head" style="margin-bottom:20px;">
        <div>
          <h1 style="font-size:1.5rem;">{{ $utilisateur->nom }} {{ $utilisateur->prenom }}</h1>
          <p>{{ ucfirst(str_replace('_', ' ', $utilisateur->role)) }} — inscrit le {{ $utilisateur->date_inscription->format('d/m/Y') }}</p>
        </div>
        <x-status-pill :status="$utilisateur->statut" />
      </div>

      <div class="form-grid" style="margin-bottom:24px;">
        <div class="form-field">
          <label>Email</label>
          <p><b>{{ $utilisateur->email }}</b></p>
        </div>
        <div class="form-field">
          <label>Téléphone</label>
          <p><b>{{ $utilisateur->telephone ?? '—' }}</b></p>
        </div>
        <div class="form-field full">
          <label>Adresse</label>
          <p><b>{{ $utilisateur->adresse ?? 'Non renseignée' }}</b></p>
        </div>
      </div>

      @if ($profil)
        <h3 style="font-size:1rem;margin-bottom:10px;">Informations spécifiques au rôle</h3>
        <div class="form-grid" style="margin-bottom:24px;">
          @foreach ($profil->getAttributes() as $champ => $valeur)
            @continue(in_array($champ, ['id_utilisateur', 'created_at', 'updated_at', 'deleted_at']))
            <div class="form-field">
              <label>{{ ucfirst(str_replace('_', ' ', $champ)) }}</label>
              <p><b>{{ $valeur ?? '—' }}</b></p>
            </div>
          @endforeach
        </div>
      @endif

      @if ($utilisateur->role !== 'administrateur')
        <div class="dash-actions">
          @if ($utilisateur->statut === 'actif')
            <form method="POST" action="{{ route('mon-espace.admin.utilisateurs.suspendre', $utilisateur) }}" onsubmit="return confirm('Suspendre ce compte ?');">
              @csrf
              <button type="submit" class="btn btn-danger">Suspendre</button>
            </form>
          @else
            <form method="POST" action="{{ route('mon-espace.admin.utilisateurs.reactiver', $utilisateur) }}">
              @csrf
              <button type="submit" class="btn btn-primary">Réactiver</button>
            </form>
          @endif
        </div>
      @endif
    </div>
  </div>
</section>
@endsection
