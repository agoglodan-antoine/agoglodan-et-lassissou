@extends('layouts.app')

@section('title', 'Mes annonces — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Mes annonces</h1>
        @php($typeRole = \App\Models\Annonce::typeAttenduPourRole(auth()->user()->role))
        <p>Publiez, modifiez et suivez le statut de vos annonces {{ $typeRole === 'animal' ? "d'animaux" : 'de '.$typeRole }}.</p>
      </div>
      <a href="{{ route('annonces.create') }}" class="btn btn-primary">+ Nouvelle annonce</a>
    </div>

    @if (session('status'))
      <div class="dash-card" style="background:var(--pasture-soft);border:none;color:var(--pasture-dark);font-weight:600;">
        {{ session('status') }}
      </div>
    @endif
    @if ($errors->any())
      <div class="dash-card" style="background:rgba(189,74,30,0.08);border:none;color:var(--clay-dark);font-weight:600;">
        {{ $errors->first() }}
      </div>
    @endif

    <div class="dash-card" style="padding:0;">
      @if ($annonces->isEmpty())
        <div class="empty-state">
          Vous n'avez encore publié aucune annonce.
          <br><a href="{{ route('annonces.create') }}" class="btn btn-ghost-dark" style="margin-top:16px;display:inline-flex;">Publier ma première annonce</a>
        </div>
      @else
        <div class="table-responsive">
<table class="dash-table">
          <thead>
            <tr>
              <th>Annonce</th>
              <th>Prix</th>
              <th>Quantité</th>
              <th>Statut</th>
              <th>Publiée le</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($annonces as $annonce)
              <tr>
                <td style="display:flex;align-items:center;gap:12px;">
                  <img class="dash-thumb" src="{{ $annonce->image_1 ? asset('storage/'.$annonce->image_1) : '' }}" alt="">
                  <div>
                    <b>{{ $annonce->titre }}</b>
                    @if ($annonce->statut === 'rejetee' && $annonce->motif_rejet)
                      <div style="font-size:0.78rem;color:var(--clay-dark);">Motif : {{ $annonce->motif_rejet }}</div>
                    @endif
                  </div>
                </td>
                <td>{{ number_format($annonce->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                <td>{{ $annonce->quantite }}</td>
                <td><span class="status-pill {{ $annonce->statut }}">{{ str_replace('_', ' ', $annonce->statut) }}</span></td>
                <td>{{ $annonce->date_publication->format('d/m/Y') }}</td>
                <td>
                  <div class="dash-actions">
                    <a href="{{ route('annonces.edit', $annonce) }}" class="btn btn-ghost-dark btn-sm">Modifier</a>
                    <form method="POST" action="{{ route('annonces.destroy', $annonce) }}" onsubmit="return confirm('Supprimer définitivement cette annonce ?');">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
</div>
      @endif
    </div>

    {{ $annonces->links() }}
  </div>
</section>
@endsection
