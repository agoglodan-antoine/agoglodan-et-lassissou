@extends('layouts.monEspace')

@section('title', $annonce->titre.' — ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:800px;">
    <div class="dash-card">
      <x-back-link :href="route('mon-espace.annonces.index')" label="Retour à Mon catalogue" />

      @if (session('status'))
        <div class="dash-card" style="background:var(--pasture-soft);box-shadow:none;color:var(--pasture-dark);font-weight:600;">
          {{ session('status') }}
        </div>
      @endif

      <div class="dash-head" style="margin-bottom:20px;">
        <div>
          <h1 style="font-size:1.5rem;">{{ $annonce->titre }}</h1>
          <p>{{ ucfirst($annonce->type_annonce) }} — publiée le {{ $annonce->date_publication->format('d/m/Y') }}</p>
        </div>
        <x-status-pill :status="$annonce->statut" />
      </div>

      @if ($annonce->statut === 'rejetee' && $annonce->motif_rejet)
        <div class="dash-card" style="background:rgba(189,74,30,0.08);box-shadow:none;color:var(--clay-dark);font-weight:600;">
          Motif du rejet : {{ $annonce->motif_rejet }}
        </div>
      @endif

      <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:20px;">
        @if ($annonce->image_1)
          <img src="{{ asset('storage/'.$annonce->image_1) }}" alt="" style="width:200px;height:150px;object-fit:cover;border-radius:var(--radius-sm);background:var(--sand);">
        @endif
        @if ($annonce->image_2)
          <img src="{{ asset('storage/'.$annonce->image_2) }}" alt="" style="width:200px;height:150px;object-fit:cover;border-radius:var(--radius-sm);background:var(--sand);">
        @endif
      </div>

      @if ($annonce->description)
        <p style="color:var(--ink-soft);margin-bottom:20px;">{{ $annonce->description }}</p>
      @endif

      <div class="form-grid" style="margin-bottom:20px;">
        <div class="form-field">
          <label>Prix unitaire</label>
          <p><b>{{ number_format($annonce->prix_unitaire, 0, ',', ' ') }} FCFA</b></p>
        </div>
        <div class="form-field">
          <label>Quantité disponible</label>
          <p><b>{{ $annonce->quantite }}</b></p>
        </div>
        @if ($annonce->type_annonce === 'animal')
          <div class="form-field">
            <label>Poids</label>
            <p><b>{{ $annonce->poids }} kg</b></p>
          </div>
          <div class="form-field">
            <label>Âge</label>
            <p><b>{{ $annonce->mois }} mois</b></p>
          </div>
        @else
          <div class="form-field">
            <label>Unité de mesure</label>
            <p><b>{{ $annonce->unite_de_mesure }}</b></p>
          </div>
        @endif
        <div class="form-field">
          <label>Disponibilité</label>
          <p><b>{{ $annonce->etat === 'disponible' ? 'Disponible' : 'Stock épuisé' }}</b></p>
        </div>
      </div>

      @if ($annonce->reductions->isNotEmpty())
        <h3 style="font-size:1rem;margin-bottom:10px;">Réductions par quantité</h3>
        <div class="table-responsive" style="margin-bottom:20px;">
          <table class="dash-table">
            <thead><tr><th>Quantité min.</th><th>Quantité max.</th><th>Réduction</th></tr></thead>
            <tbody>
              @foreach ($annonce->reductions as $reduction)
                <tr>
                  <td>{{ $reduction->quantite_min }}</td>
                  <td>{{ $reduction->quantite_max }}</td>
                  <td>{{ $reduction->pourcentage_reduction }}%</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

      <div class="dash-actions">
        <a href="{{ route('mon-espace.annonces.edit', $annonce) }}" class="btn btn-primary">Modifier</a>
        @if ($annonce->statut === 'visible')
          <a href="{{ route('catalogue.show', $annonce) }}" class="btn btn-ghost-dark">Voir la fiche publique</a>
        @endif
        <form method="POST" action="{{ route('mon-espace.annonces.destroy', $annonce) }}" onsubmit="return confirm('Supprimer définitivement cette annonce ?');">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
