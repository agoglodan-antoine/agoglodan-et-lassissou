@extends('layouts.app')

@section('title', 'Paiement sécurisé — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="auth-card" style="max-width:560px;">
      <x-back-link :href="route('mon-espace.commandes.show', $commande)" label="Retour à la commande" />
      <h1>Paiement sécurisé</h1>
      <p class="sub">Commande {{ $commande->code_authenticite }} — {{ $commande->annonce->titre }}</p>

      <div class="dash-card" style="background:var(--sand);box-shadow:none;padding:20px;margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;">
          <span>Montant à régler</span>
          <b style="font-size:1.2rem;">{{ number_format($commande->montant_net_commande, 0, ',', ' ') }} FCFA</b>
        </div>
      </div>

      <div class="geoloc-status" style="margin-bottom:20px;">
        <i class="fa-solid fa-lock"></i> Ce montant sera détenu en séquestre par ElevConnect et versé au fournisseur
        uniquement après votre confirmation de réception par code QR.
      </div>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('paiement.process', $commande) }}">
        @csrf
        <div class="form-field">
          <label for="moyen_de_paiement">Moyen de paiement</label>
          <select id="moyen_de_paiement" name="moyen_de_paiement" required>
            <option value="mobile_money">Mobile Money</option>
            <option value="carte_bancaire">Carte bancaire</option>
          </select>
        </div>
        <div class="form-field">
          <label for="numero_de_compte">Numéro de compte / téléphone</label>
          <input type="text" id="numero_de_compte" name="numero_de_compte" required placeholder="Ex. 01 00 00 00 00">
        </div>

        <button type="submit" class="btn btn-primary auth-submit">Payer {{ number_format($commande->montant_net_commande, 0, ',', ' ') }} FCFA</button>
      </form>

      <p class="form-hint" style="margin-top:16px;text-align:center;">
        Paiement traité par une passerelle Mobile Money / carte bancaire à intégrer en production.
      </p>
    </div>
  </div>
</section>
@endsection
