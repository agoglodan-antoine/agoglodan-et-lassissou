@extends('layouts.monEspace')

@section('title', 'Mon abonnement — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <x-back-link :href="route('mon-espace.dashboard')" label="Retour au tableau de bord" />
    <div class="dash-head">
      <div>
        <h1>Mon abonnement</h1>
        <p>Formule actuelle : <b>{{ $estPremium ? 'Premium' : 'Basique' }}</b>
          @if ($abonnementActif && $estPremium)
            — expire le {{ $abonnementActif->date_expiration->format('d/m/Y') }}
          @endif
        </p>
      </div>
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

    <div class="pricing-grid" style="margin-bottom:32px;">
      <div class="price-card">
        <h3>Basique</h3>
        <p class="price-desc" style="color:var(--ink-soft);margin-top:6px;">
          Jusqu'à {{ config('elevconnect.abonnement_veterinaire.basique.limite_services') }} services actifs
        </p>
        <div class="price-amount">Gratuit</div>
        @if (! $estPremium)
          <x-status-pill force="visible" label="Formule actuelle" style="margin-top:20px;display:inline-block;" />
        @else
          <form method="POST" action="{{ route('mon-espace.abonnement.souscrire') }}" style="margin-top:20px;">
            @csrf
            <input type="hidden" name="formule" value="basique">
            <button type="submit" class="btn btn-ghost-dark">Revenir au Basique</button>
          </form>
        @endif
      </div>

      <div class="price-card premium">
        <span class="plan-badge">Recommandé</span>
        <h3>Premium</h3>
        <p class="price-desc">Services illimités, mise en avant, statistiques</p>
        <div class="price-amount">2 000 FCFA<sub>/mois</sub></div>

        @if ($estPremium)
          <x-status-pill force="visible" label="Formule actuelle" style="margin-top:20px;display:inline-block;" />
        @else
          <button type="button" class="btn btn-primary" id="showPremiumForm" style="margin-top:20px;">Passer en Premium</button>
        @endif
      </div>
    </div>

    @if (! $estPremium)
      <div class="auth-card" id="premiumForm" style="display:none;max-width:480px;">
        <h3 style="font-size:1.1rem;margin-bottom:16px;">Paiement de l'abonnement Premium</h3>
        <form method="POST" action="{{ route('mon-espace.abonnement.souscrire') }}">
          @csrf
          <input type="hidden" name="formule" value="premium">
          <div class="form-field">
            <label for="moyen_de_paiement">Moyen de paiement</label>
            <select id="moyen_de_paiement" name="moyen_de_paiement" required>
              <option value="mobile_money">Mobile Money</option>
              <option value="carte_bancaire">Carte bancaire</option>
            </select>
          </div>
          <div class="form-field">
            <label for="numero_de_compte">Numéro de compte / téléphone</label>
            <input type="text" id="numero_de_compte" name="numero_de_compte" required>
          </div>
          <button type="submit" class="btn btn-primary auth-submit">Payer 2 000 FCFA</button>
        </form>
      </div>
    @endif

    @if ($historique->isNotEmpty())
      <h3 style="font-size:1.1rem;margin:32px 0 14px;">Historique</h3>
      <div class="dash-card" style="padding:0;">
        <div class="table-responsive">
<table class="dash-table">
          <thead><tr><th>Formule</th><th>Début</th><th>Expiration</th><th>Statut</th></tr></thead>
          <tbody>
            @foreach ($historique as $ab)
              <tr>
                <td>{{ ucfirst($ab->formule) }}</td>
                <td>{{ $ab->date_debut->format('d/m/Y') }}</td>
                <td>{{ $ab->date_expiration->format('d/m/Y') }}</td>
                <td><x-status-pill :status="$ab->statut" /></td>
              </tr>
            @endforeach
          </tbody>
        </table>
</div>
      </div>
    @endif
  </div>
</section>
@endsection

@push('scripts')
<script>
  const btn = document.getElementById('showPremiumForm');
  if (btn) {
    btn.addEventListener('click', function(){
      document.getElementById('premiumForm').style.display = 'block';
      btn.style.display = 'none';
    });
  }
</script>
@endpush
