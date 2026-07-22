@extends('layouts.mon-espace')

@section('title', 'Livraisons proposées — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Livraisons proposées</h1>
        <p>Commandes pour lesquelles un acheteur vous a choisi comme livreur — acceptez ou refusez chacune.</p>
      </div>
      <a href="{{ route('mon-espace.livraisons.mes') }}" class="btn btn-ghost-dark">Mes livraisons en cours</a>
    </div>

    @if ($errors->any())
      <div class="dash-card" style="background:rgba(189,74,30,0.08);border:none;color:var(--clay-dark);font-weight:600;">
        {{ $errors->first() }}
      </div>
    @endif

    @if ($livraisons->isEmpty())
      <div class="empty-state">Aucune livraison ne vous est proposée pour le moment.</div>
    @else
      @foreach ($livraisons as $livraison)
        <div class="moderation-card">
          <div style="flex:1;">
            <h4 style="margin-bottom:4px;">{{ $livraison->commande->annonce->titre }} — {{ $livraison->commande->quantite }} unité(s)</h4>
            <div style="font-size:0.85rem;color:var(--ink-soft);margin-bottom:14px;">
              <b>Enlèvement :</b> {{ $livraison->adresse_fournisseur }} &nbsp;→&nbsp;
              <b>Livraison :</b> {{ $livraison->adresse_client }}
            </div>

            <div class="dash-actions" style="align-items:flex-end;">
              <form method="POST" action="{{ route('mon-espace.livraisons.accepter', $livraison) }}" style="display:flex;gap:10px;align-items:flex-end;">
                @csrf
                <div class="form-field" style="margin-bottom:0;">
                  <label>Vos frais de livraison (FCFA)</label>
                  <input type="number" name="frais_de_livraison" min="0" step="50" required style="width:160px;">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Accepter</button>
              </form>

              <form method="POST" action="{{ route('mon-espace.livraisons.rejeter', $livraison) }}" onsubmit="return confirm('Refuser cette livraison ? Elle sera proposée à un autre livreur disponible.');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Refuser</button>
              </form>
            </div>
          </div>
        </div>
      @endforeach

      {{ $livraisons->links() }}
    @endif
  </div>
</section>
@endsection
