@extends('layouts.monEspace')

@section('title', 'Livraison #'.$livraison->id_livraison.' — ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:700px;">
    <div class="dash-card">
      <x-back-link
        :href="$livraison->statut === 'en_attente' ? route('mon-espace.livraison.proposees') : route('mon-espace.livraison.mes')"
        :label="$livraison->statut === 'en_attente' ? 'Retour aux livraisons proposées' : 'Retour à mes livraisons'"
      />

      @if (session('status'))
        <div class="dash-card" style="background:var(--pasture-soft);box-shadow:none;color:var(--pasture-dark);font-weight:600;">
          {{ session('status') }}
        </div>
      @endif

      <div class="dash-head" style="margin-bottom:20px;">
        <div>
          <h1 style="font-size:1.5rem;">{{ $livraison->commande->annonce->titre }}</h1>
          <p>{{ $livraison->commande->quantite }} unité(s) — commande {{ $livraison->commande->code_authenticite }}</p>
        </div>
        <x-status-pill :status="$livraison->statut" />
      </div>

      <div class="form-grid" style="margin-bottom:20px;">
        <div class="form-field full">
          <label>Enlèvement</label>
          <p><b>{{ $livraison->adresse_fournisseur }}</b></p>
        </div>
        <div class="form-field full">
          <label>Livraison</label>
          <p><b>{{ $livraison->adresse_client }}</b></p>
        </div>
        <div class="form-field">
          <label>Acheteur</label>
          <p><b>{{ $livraison->commande->acheteur->nom }} {{ $livraison->commande->acheteur->prenom }}</b></p>
        </div>
        @if ($livraison->montant_net_livraison)
          <div class="form-field">
            <label>Frais nets</label>
            <p><b>{{ number_format($livraison->montant_net_livraison, 0, ',', ' ') }} FCFA</b></p>
          </div>
        @endif
      </div>

      <div class="dash-actions">
        @if ($livraison->statut === 'en_attente')
          <form method="POST" action="{{ route('mon-espace.livraison.accepter', $livraison) }}" style="display:flex;gap:10px;align-items:flex-end;">
            @csrf
            <div class="form-field" style="margin-bottom:0;">
              <label>Vos frais de livraison (FCFA)</label>
              <input type="number" name="frais_de_livraison" min="0" step="50" required style="width:160px;">
            </div>
            <button type="submit" class="btn btn-primary">Accepter</button>
          </form>
          <form method="POST" action="{{ route('mon-espace.livraison.rejeter', $livraison) }}" onsubmit="return confirm('Refuser cette livraison ? Elle sera proposée à un autre livreur disponible.');">
            @csrf
            <button type="submit" class="btn btn-danger">Refuser</button>
          </form>
        @endif

        @if ($livraison->statut === 'prise_en_charge')
          <form method="POST" action="{{ route('mon-espace.livraison.demarrer', $livraison) }}">
            @csrf
            <button type="submit" class="btn btn-primary">Démarrer la course</button>
          </form>
        @endif

        @if ($livraison->statut === 'en_cours' && $livraison->commande->statut !== 'livree')
          <form method="POST" action="{{ route('mon-espace.livraison.livrer', $livraison) }}">
            @csrf
            <button type="submit" class="btn btn-primary">Livraison terminée</button>
          </form>
        @endif

        @if ($livraison->statut === 'en_cours' && $livraison->commande->statut === 'livree')
          <span class="form-hint">En attente de la confirmation de l'acheteur.</span>
        @endif

        @if ($livraison->statut === 'terminee')
          <span class="form-hint">Réception confirmée par l'acheteur <i class="fa-solid fa-check"></i></span>
        @endif
      </div>

      @if ($livraison->statut === 'en_cours' && $livraison->commande->statut === 'livree')
        <div class="dash-card" style="background:var(--sand);box-shadow:none;text-align:center;margin-top:20px;">
          <h3 style="font-size:1.05rem;margin-bottom:6px;">Faites scanner ce code par l'acheteur</h3>
          <p class="form-hint" style="margin-bottom:16px;">
            Montrez cet écran à l'acheteur : il doit le scanner avec son téléphone pour confirmer
            qu'il a bien reçu sa commande. Le code n'est valable que pour cette livraison.
          </p>
          <div style="display:inline-block;padding:16px;background:var(--white);border-radius:var(--radius-sm);">
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(220)->generate(\Illuminate\Support\Facades\Crypt::encryptString($livraison->commande->code_authenticite)) !!}
          </div>
        </div>
      @endif
    </div>
  </div>
</section>
@endsection
