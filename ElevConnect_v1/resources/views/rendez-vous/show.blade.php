@extends('layouts.mon-espace')

@section('title', 'Rendez-vous — ElevConnect')

@php
  $estVeterinaire = auth()->id() === $rendezVous->id_veterinaire;
@endphp

@section('content')
<a href="{{ $estVeterinaire ? route('mon-espace.rendez-vous-recus.index') : route('mon-espace.rendez-vous.index') }}" class="me-back">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
  Retour à mes rendez-vous
</a>

<div class="dash-card" style="max-width:700px;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <h1 style="font-size:1.5rem;">{{ $rendezVous->sujet }}</h1>
    <span class="status-pill {{ in_array($rendezVous->statut, ['confirme','realise']) ? 'visible' : (in_array($rendezVous->statut, ['annule','refuse']) ? 'rejetee' : 'en_attente') }}">
      {{ str_replace('_', ' ', $rendezVous->statut) }}
    </span>
  </div>

  <div class="form-grid" style="margin-bottom:20px;">
    <div><b>Éleveur :</b> {{ $rendezVous->eleveur->utilisateur->nom }} {{ $rendezVous->eleveur->utilisateur->prenom }}</div>
    <div><b>Vétérinaire :</b> Dr {{ $rendezVous->veterinaire->utilisateur->nom }} {{ $rendezVous->veterinaire->utilisateur->prenom }}</div>
    <div><b>Date souhaitée :</b> {{ $rendezVous->date_prevue->format('d/m/Y à H:i') }}</div>
    @if ($rendezVous->service)
      <div><b>Service :</b> {{ $rendezVous->service->titre_service }} ({{ number_format($rendezVous->service->prix, 0, ',', ' ') }} FCFA)</div>
    @endif
  </div>

  @if ($rendezVous->description)
    <p style="color:var(--ink-soft);margin-bottom:20px;">{{ $rendezVous->description }}</p>
  @endif

  <p class="form-hint" style="margin-bottom:20px;">Le paiement de la consultation se règle directement avec le vétérinaire, hors plateforme.</p>

  <div class="dash-actions">
    @if ($estVeterinaire)
      @if ($rendezVous->statut === 'en_attente')
        <form method="POST" action="{{ route('mon-espace.rendez-vous-recus.confirmer', $rendezVous) }}">
          @csrf
          <button type="submit" class="btn btn-primary btn-sm">Confirmer</button>
        </form>
        <form method="POST" action="{{ route('mon-espace.rendez-vous-recus.refuser', $rendezVous) }}">
          @csrf
          <button type="submit" class="btn btn-danger btn-sm">Refuser</button>
        </form>
      @endif
      @if ($rendezVous->statut === 'confirme')
        <form method="POST" action="{{ route('mon-espace.rendez-vous-recus.realise', $rendezVous) }}">
          @csrf
          <button type="submit" class="btn btn-primary btn-sm">Marquer réalisé</button>
        </form>
      @endif
    @else
      @if (in_array($rendezVous->statut, ['en_attente', 'confirme']))
        <form method="POST" action="{{ route('mon-espace.rendez-vous.annuler', $rendezVous) }}" onsubmit="return confirm('Annuler ce rendez-vous ?');">
          @csrf
          <button type="submit" class="btn btn-danger btn-sm">Annuler</button>
        </form>
      @endif
    @endif
  </div>
</div>
@endsection
