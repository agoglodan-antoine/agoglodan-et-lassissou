@extends('layouts.mon-espace')

@section('title', 'Livraison #'.$livraison->id_livraison.' — ElevConnect')

@section('content')
<a href="{{ route('mon-espace.livraisons.mes') }}" class="me-back">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
  Retour à mes livraisons
</a>

<div class="dash-card" style="max-width:700px;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 style="font-size:1.5rem;">Livraison #{{ $livraison->id_livraison }}</h1>
    <span class="status-pill {{ $livraison->statut === 'terminee' ? 'visible' : ($livraison->statut === 'rejetee' ? 'rejetee' : 'en_attente') }}">
      {{ str_replace('_', ' ', $livraison->statut) }}
    </span>
  </div>

  <div class="form-grid" style="margin-bottom:24px;">
    <div style="grid-column:1/-1;"><b>Annonce :</b> {{ $livraison->commande->annonce->titre }} — {{ $livraison->commande->quantite }} unité(s)</div>
    <div><b>Enlèvement :</b> {{ $livraison->adresse_fournisseur }}</div>
    <div><b>Livraison :</b> {{ $livraison->adresse_client }}</div>
    <div><b>Acheteur :</b> {{ $livraison->commande->acheteur->nom }} {{ $livraison->commande->acheteur->prenom }}</div>
    <div><b>Fournisseur :</b> {{ $livraison->commande->annonce->auteur->nom }} {{ $livraison->commande->annonce->auteur->prenom }}</div>
    <div><b>Frais bruts :</b> {{ number_format($livraison->frais_de_livraison, 0, ',', ' ') }} FCFA</div>
    <div><b>Réduction :</b> {{ number_format($livraison->reduction_sur_frais, 0, ',', ' ') }} FCFA</div>
    <div style="font-size:1.1rem;"><b>Frais nets :</b> {{ number_format($livraison->montant_net_livraison, 0, ',', ' ') }} FCFA</div>
  </div>

  @if ($livraison->note_client_livraison)
    <div class="dash-card" style="background:var(--sand);box-shadow:none;">
      <b>Avis client :</b> {{ $livraison->note_client_livraison }}/5
      @if ($livraison->avis_client_livraison)
        <p style="margin-top:6px;color:var(--ink-soft);">{{ $livraison->avis_client_livraison }}</p>
      @endif
    </div>
  @endif

  <div class="dash-actions" style="margin-top:20px;">
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
  </div>
</div>
@endsection
