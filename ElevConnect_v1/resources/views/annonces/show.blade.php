@extends('layouts.mon-espace')

@section('title', $annonce->titre.' — ElevConnect')

@section('content')
<a href="{{ route('mon-espace.annonces.index') }}" class="me-back">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
  Retour à mes annonces
</a>

<div class="dash-card" style="max-width:760px;">
  <img src="{{ $annonce->image_1 ? asset('storage/'.$annonce->image_1) : '' }}" alt="{{ $annonce->titre }}"
       style="width:100%;max-height:360px;object-fit:cover;border-radius:var(--radius-sm);margin-bottom:24px;background:var(--sand);">

  <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
    <span class="status-pill {{ $annonce->statut }}">{{ str_replace('_', ' ', $annonce->statut) }}</span>
    <span class="status-pill visible">{{ ucfirst($annonce->type_annonce) }}</span>
  </div>

  <h1 style="margin-bottom:8px;">{{ $annonce->titre }}</h1>
  <div class="price" style="font-size:1.3rem;margin-bottom:20px;">{{ number_format($annonce->prix_unitaire, 0, ',', ' ') }} FCFA</div>

  @if ($annonce->statut === 'rejetee' && $annonce->motif_rejet)
    <div class="dash-card" style="background:rgba(189,74,30,0.08);box-shadow:none;color:var(--clay-dark);margin-bottom:20px;">
      <b>Motif du rejet :</b> {{ $annonce->motif_rejet }}
    </div>
  @endif

  @if ($annonce->description)
    <p style="color:var(--ink-soft);margin-bottom:20px;">{{ $annonce->description }}</p>
  @endif

  <div class="form-grid" style="margin-bottom:20px;">
    @if ($annonce->type_annonce === 'animal')
      <div><b>Poids :</b> {{ $annonce->poids }} kg</div>
      <div><b>Âge :</b> {{ $annonce->mois }} mois</div>
    @else
      <div><b>Unité :</b> {{ $annonce->unite_de_mesure }}</div>
    @endif
    <div><b>Quantité disponible :</b> {{ $annonce->quantite }}</div>
    <div><b>État :</b> {{ $annonce->etat === 'disponible' ? 'Disponible' : 'Stock épuisé' }}</div>
    <div><b>Publiée le :</b> {{ $annonce->date_publication->format('d/m/Y') }}</div>
    <div><b>Commandes reçues :</b> {{ $annonce->commandes->count() }}</div>
  </div>

  @if ($annonce->reductions->isNotEmpty())
    <h3 style="font-size:1.05rem;margin-bottom:10px;">Réductions par quantité</h3>
    <div class="table-responsive">
    <table class="dash-table" style="margin-bottom:24px;">
      <thead><tr><th>Quantité</th><th>Réduction</th></tr></thead>
      <tbody>
        @foreach ($annonce->reductions as $reduction)
          <tr>
            <td>{{ $reduction->quantite_min }} – {{ $reduction->quantite_max }}</td>
            <td>{{ rtrim(rtrim(number_format($reduction->pourcentage_reduction, 2), '0'), '.') }}%</td>
          </tr>
        @endforeach
      </tbody>
    </table>
    </div>
  @endif

  <div class="dash-actions">
    <a href="{{ route('mon-espace.annonces.edit', $annonce) }}" class="btn btn-ghost-dark btn-sm">Modifier</a>
    <form method="POST" action="{{ route('mon-espace.annonces.destroy', $annonce) }}" onsubmit="return confirm('Supprimer définitivement cette annonce ?');">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
    </form>
  </div>
</div>
@endsection
