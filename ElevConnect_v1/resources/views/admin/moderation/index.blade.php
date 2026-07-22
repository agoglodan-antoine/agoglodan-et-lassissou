@extends('layouts.mon-espace')

@section('title', 'Modération des annonces — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Modération des annonces</h1>
        <p>Annonces en attente d'approbation avant publication sur le catalogue public.</p>
      </div>
    </div>


    @if ($annonces->isEmpty())
      <div class="empty-state">Aucune annonce en attente de modération. 🎉</div>
    @else
      @foreach ($annonces as $annonce)
        <div class="moderation-card">
          <img src="{{ $annonce->image_1 ? asset('storage/'.$annonce->image_1) : '' }}" alt="">
          <div style="flex:1;">
            <span class="status-pill en_attente">{{ ucfirst($annonce->type_annonce) }}</span>
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
              <form method="POST" action="{{ route('admin.moderation.approuver', $annonce) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">Approuver</button>
              </form>
            </div>

            <form method="POST" action="{{ route('admin.moderation.rejeter', $annonce) }}" class="moderation-reject-form">
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
