@extends('layouts.monEspace')

@section('title', 'Modération des annonces — ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container">
    @include('admin._nav')
    <div class="dash-head">
      <div>
        <h1>Modération des annonces</h1>
        <p>Annonces en attente d'approbation avant publication sur le catalogue public.</p>
      </div>
    </div>

    @if (session('status'))
      <div class="dash-card" style="background:var(--pasture-soft);border:none;color:var(--pasture-dark);font-weight:600;">
        {{ session('status') }}
      </div>
    @endif

    @if ($annonces->isEmpty())
      <div class="empty-state"><i class="fa-solid fa-circle-check"></i> Aucune annonce en attente de modération.</div>
    @else
      @foreach ($annonces as $annonce)
        <div class="moderation-card">
          <img src="{{ $annonce->image_1 ? asset('storage/'.$annonce->image_1) : '' }}" alt="">
          <div style="flex:1;">
            <x-status-pill force="en_attente" :label="ucfirst($annonce->type_annonce)" />
            <h4 style="margin:8px 0 4px;">{{ $annonce->titre }}</h4>
            <div style="font-size:0.85rem;color:var(--ink-soft);margin-bottom:10px;">
              Par {{ $annonce->auteur->nom }} {{ $annonce->auteur->prenom }} —
              {{ number_format($annonce->prix_unitaire, 0, ',', ' ') }} FCFA —
              publiée le {{ $annonce->date_publication->format('d/m/Y à H:i') }}
            </div>
            @if ($annonce->description)
              <p style="font-size:0.88rem;color:var(--ink-soft);margin-bottom:12px;">{{ Str::limit($annonce->description, 160) }}</p>
            @endif

            <div class="dash-actions">
              <form method="POST" action="{{ route('mon-espace.admin.moderation.approuver', $annonce) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">Approuver</button>
              </form>
            </div>

            <form method="POST" action="{{ route('mon-espace.admin.moderation.rejeter', $annonce) }}" class="moderation-reject-form">
              @csrf
              <input type="text" name="motif_rejet" placeholder="Motif de rejet (obligatoire)" required>
              <button type="submit" class="btn btn-danger btn-sm">Rejeter</button>
            </form>
          </div>
        </div>
      @endforeach

      {{ $annonces->links() }}
    @endif
  </div>
</section>
@endsection
