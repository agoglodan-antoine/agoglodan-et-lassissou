@extends('layouts.monEspace')

@section('title', 'Rendez-vous — ElevConnect')

@php
  $estVeterinaire = auth()->id() === $rendezVous->id_veterinaire;
  $backHref = $estVeterinaire ? route('mon-espace.rendez-vous-recus.index') : route('mon-espace.rendez-vous.index');
  $backLabel = $estVeterinaire ? 'Retour aux rendez-vous reçus' : 'Retour à mes rendez-vous';
@endphp

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:700px;">
    <div class="dash-card">
      <x-back-link :href="$backHref" :label="$backLabel" />

      @if (session('status'))
        <div class="dash-card" style="background:var(--pasture-soft);box-shadow:none;color:var(--pasture-dark);font-weight:600;">
          {{ session('status') }}
        </div>
      @endif

      <div class="dash-head" style="margin-bottom:20px;">
        <div>
          <h1 style="font-size:1.5rem;">{{ $rendezVous->sujet }}</h1>
          <p>{{ $rendezVous->date_prevue->format('d/m/Y à H:i') }}</p>
        </div>
        <x-status-pill :status="$rendezVous->statut" />
      </div>

      <div class="form-grid" style="margin-bottom:20px;">
        <div class="form-field">
          <label>Éleveur</label>
          <p><b>{{ $rendezVous->eleveur->utilisateur->nom }} {{ $rendezVous->eleveur->utilisateur->prenom }}</b></p>
        </div>
        <div class="form-field">
          <label>Vétérinaire</label>
          <p><b>Dr {{ $rendezVous->veterinaire->utilisateur->nom }} {{ $rendezVous->veterinaire->utilisateur->prenom }}</b></p>
        </div>
        @if ($rendezVous->service)
          <div class="form-field full">
            <label>Service concerné</label>
            <p><b>{{ $rendezVous->service->titre_service }}</b> — {{ number_format($rendezVous->service->prix, 0, ',', ' ') }} FCFA</p>
          </div>
        @endif
      </div>

      @if ($rendezVous->description)
        <p style="color:var(--ink-soft);margin-bottom:20px;">{{ $rendezVous->description }}</p>
      @endif

      <p class="form-hint" style="margin-bottom:20px;">Le paiement de la consultation se règle directement avec le vétérinaire, hors plateforme.</p>

      @if ($rendezVous->note_client)
        <div class="dash-card" style="background:var(--sand);box-shadow:none;">
          <h3 style="font-size:1.05rem;margin-bottom:6px;">Votre avis</h3>
          <p><i class="fa-solid fa-star"></i> {{ $rendezVous->note_client }}/5</p>
          @if ($rendezVous->avis_client)
            <p style="color:var(--ink-soft);">{{ $rendezVous->avis_client }}</p>
          @endif
        </div>
      @elseif (! $estVeterinaire && $rendezVous->statut === 'realise')
        <div class="dash-card" style="background:var(--sand);box-shadow:none;">
          <h3 style="font-size:1.05rem;margin-bottom:10px;">Votre avis</h3>
          <form method="POST" action="{{ route('mon-espace.rendez-vous.noter', $rendezVous) }}">
            @csrf
            <div class="form-field">
              <label>Note du vétérinaire (1 à 5)</label>
              <select name="note_client" required>
                @for ($i = 5; $i >= 1; $i--)
                  <option value="{{ $i }}">{{ $i }} {{ $i > 1 ? 'étoiles' : 'étoile' }}</option>
                @endfor
              </select>
            </div>
            <div class="form-field">
              <label>Commentaire (facultatif)</label>
              <textarea name="avis_client" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Envoyer mon avis</button>
          </form>
        </div>
      @endif

      <div class="dash-actions">
        @if (! $estVeterinaire && in_array($rendezVous->statut, ['en_attente', 'confirme']))
          <form method="POST" action="{{ route('mon-espace.rendez-vous.annuler', $rendezVous) }}" onsubmit="return confirm('Annuler ce rendez-vous ?');">
            @csrf
            <button type="submit" class="btn btn-danger">Annuler</button>
          </form>
        @endif

        @if ($estVeterinaire && $rendezVous->statut === 'en_attente')
          <form method="POST" action="{{ route('mon-espace.rendez-vous-recus.confirmer', $rendezVous) }}">
            @csrf
            <button type="submit" class="btn btn-primary">Confirmer</button>
          </form>
          <form method="POST" action="{{ route('mon-espace.rendez-vous-recus.refuser', $rendezVous) }}">
            @csrf
            <button type="submit" class="btn btn-danger">Refuser</button>
          </form>
        @endif

        @if ($estVeterinaire && $rendezVous->statut === 'confirme')
          <form method="POST" action="{{ route('mon-espace.rendez-vous-recus.realise', $rendezVous) }}">
            @csrf
            <button type="submit" class="btn btn-primary">Marquer réalisé</button>
          </form>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
