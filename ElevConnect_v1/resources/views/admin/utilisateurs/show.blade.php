@extends('layouts.mon-espace')

@section('title', $utilisateur->nom.' '.$utilisateur->prenom.' — ElevConnect')

@section('content')
<a href="{{ route('admin.utilisateurs.index') }}" class="me-back">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
  Retour aux utilisateurs
</a>

<div class="dash-card" style="max-width:700px;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 style="font-size:1.5rem;">{{ $utilisateur->nom }} {{ $utilisateur->prenom }}</h1>
    <span class="status-pill {{ $utilisateur->statut === 'actif' ? 'visible' : 'rejetee' }}">{{ $utilisateur->statut }}</span>
  </div>

  <div class="form-grid" style="margin-bottom:24px;">
    <div><b>Rôle :</b> {{ ucfirst(str_replace('_', ' ', $utilisateur->role)) }}</div>
    <div><b>Email :</b> {{ $utilisateur->email }}</div>
    <div><b>Téléphone :</b> {{ $utilisateur->telephone ?? '—' }}</div>
    <div><b>Adresse :</b> {{ $utilisateur->adresse ?? '—' }}</div>
    <div><b>Inscrit le :</b> {{ $utilisateur->date_inscription->format('d/m/Y') }}</div>
    <div><b>Annonces publiées :</b> {{ $utilisateur->annonces->count() }}</div>
    <div><b>Commandes passées :</b> {{ $utilisateur->commandes->count() }}</div>
  </div>

  <div class="dash-actions">
    @if ($utilisateur->role !== 'administrateur')
      @if ($utilisateur->statut === 'actif')
        <form method="POST" action="{{ route('admin.utilisateurs.suspendre', $utilisateur) }}" onsubmit="return confirm('Suspendre ce compte ?');">
          @csrf
          <button type="submit" class="btn btn-danger btn-sm">Suspendre</button>
        </form>
      @else
        <form method="POST" action="{{ route('admin.utilisateurs.reactiver', $utilisateur) }}">
          @csrf
          <button type="submit" class="btn btn-primary btn-sm">Réactiver</button>
        </form>
      @endif
    @endif
  </div>
</div>
@endsection
