@extends('layouts.app')

@section('title', $annonce->titre.' — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:900px;">
    <div class="dash-card">
      <img src="{{ $annonce->image_1 ? asset('storage/'.$annonce->image_1) : '' }}" alt="{{ $annonce->titre }}"
           style="width:100%;max-height:420px;object-fit:cover;border-radius:var(--radius-sm);margin-bottom:24px;background:var(--sand);">

      <span class="status-pill visible" style="margin-bottom:12px;">{{ ucfirst($annonce->type_annonce) }}</span>
      <h1 style="margin-bottom:8px;">{{ $annonce->titre }}</h1>
      <div class="price" style="font-size:1.4rem;margin-bottom:20px;">{{ number_format($annonce->prix_unitaire, 0, ',', ' ') }} FCFA</div>

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
        <div><b>Publiée le :</b> {{ $annonce->date_publication->format('d/m/Y') }}</div>
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

      <div class="dash-card" style="background:var(--sand);box-shadow:none;">
        <b>Fournisseur :</b> {{ $annonce->auteur->nom }} {{ $annonce->auteur->prenom }}<br>
        <b>Zone :</b> {{ $annonce->auteur->adresse ?? 'Non renseignée' }}
      </div>

      @if (! auth()->check() || auth()->id() !== $annonce->id_utilisateur)
        <a href="{{ route('commandes.create', $annonce) }}" class="btn btn-primary" style="margin-top:8px;">Commander cette annonce</a>
      @else
        <p class="form-hint" style="margin-top:8px;">Ceci est votre propre annonce.</p>
      @endif
    </div>
  </div>
</section>
@endsection
