@extends('layouts.app')

@section('title', 'Commande #'.$commande->id_commande.' — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:700px;">
    <div class="dash-card">
      <div class="dash-head" style="margin-bottom:20px;">
        <div>
          <h1 style="font-size:1.5rem;">Commande #{{ $commande->code_authenticite }}</h1>
          <p>{{ $commande->annonce->titre }}</p>
        </div>
        <span class="status-pill {{ in_array($commande->statut, ['confirmee','validee']) ? 'visible' : ($commande->statut === 'annulee' || $commande->statut === 'refusee' || $commande->statut === 'en_litige' ? 'rejetee' : 'en_attente') }}">
          {{ str_replace('_', ' ', $commande->statut) }}
        </span>
      </div>

      @if (session('status'))
        <div class="dash-card" style="background:var(--pasture-soft);box-shadow:none;color:var(--pasture-dark);font-weight:600;">
          {{ session('status') }}
        </div>
      @endif
      @if ($errors->any())
        <div class="dash-card" style="background:rgba(189,74,30,0.08);box-shadow:none;color:var(--clay-dark);font-weight:600;">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="form-grid" style="margin-bottom:24px;">
        <div><b>Quantité :</b> {{ $commande->quantite }}</div>
        <div><b>Prix unitaire :</b> {{ number_format($commande->prix_unitaire, 0, ',', ' ') }} FCFA</div>
        <div><b>Montant brut :</b> {{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</div>
        <div><b>Réduction :</b> {{ number_format($commande->reduction_sur_commande, 0, ',', ' ') }} FCFA</div>
        <div style="grid-column:1/-1;font-size:1.1rem;"><b>Montant net payé :</b> {{ number_format($commande->montant_net_commande, 0, ',', ' ') }} FCFA</div>
      </div>

      @if ($commande->statut === 'en_attente')
        <a href="{{ route('paiement.show', $commande) }}" class="btn btn-primary">Procéder au paiement</a>
      @endif

      @if ($commande->livraison)
        <div class="dash-card" style="background:var(--sand);box-shadow:none;margin-bottom:20px;">
          <b>Suivi de la livraison :</b>
          <span class="status-pill {{ $commande->livraison->statut === 'terminee' ? 'visible' : 'en_attente' }}">
            {{ str_replace('_', ' ', $commande->livraison->statut) }}
          </span>
          @if ($commande->livraison->livreur)
            <div style="margin-top:8px;font-size:0.88rem;color:var(--ink-soft);">
              Livreur : {{ $commande->livraison->livreur->utilisateur->nom ?? '' }} {{ $commande->livraison->livreur->utilisateur->prenom ?? '' }}
              — Frais nets : {{ number_format($commande->livraison->montant_net_livraison, 0, ',', ' ') }} FCFA
            </div>
          @endif
        </div>
      @elseif ($commande->estRetraitDirect() && in_array($commande->statut, ['payee', 'en_cours_de_traitement', 'validee']))
        <div class="dash-card" style="background:var(--sand);box-shadow:none;margin-bottom:20px;">
          <b>Mode de retrait :</b> retrait direct auprès du fournisseur (sans livreur).
        </div>
      @endif

      @if (! $commande->estRetraitDirect() && in_array($commande->statut, ['payee', 'en_cours_de_traitement', 'validee', 'en_cours_de_livraison']))
        <div class="dash-card" style="background:var(--sand);box-shadow:none;text-align:center;padding:28px;">
          <h3 style="font-size:1.05rem;margin-bottom:14px;">Votre code de vérification</h3>
          {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(200)->generate($commande->code_authenticite) !!}
          <p class="form-hint" style="margin-top:14px;">
            Conservez ce code : une fois la commande livrée, vous confirmerez vous-même
            la réception ici (ou en le scannant), ce qui déclenchera le versement
            au fournisseur et au livreur.
          </p>
        </div>
      @endif

      @if ($commande->estRetraitDirect() && $commande->statut === 'validee')
        <div class="dash-card" style="background:var(--sand);box-shadow:none;">
          <h3 style="font-size:1.05rem;margin-bottom:10px;">Confirmer la réception</h3>
          <p class="form-hint" style="margin-bottom:14px;">
            Votre commande a été validée par le fournisseur. Une fois le produit récupéré
            en main propre, confirmez la réception ci-dessous — aucun code n'est requis
            pour un retrait direct.
          </p>
          <form method="POST" action="{{ route('commandes.confirmer-reception', $commande) }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">J'ai récupéré ma commande</button>
          </form>

          <button type="button" class="btn btn-ghost-dark btn-sm" id="toggleDispute" style="margin-top:14px;">Signaler un problème</button>
          <form method="POST" action="{{ route('commandes.signaler-probleme', $commande) }}" id="disputeForm" style="display:none;margin-top:12px;">
            @csrf
            <textarea name="description" rows="3" placeholder="Décrivez le problème rencontré" required style="width:100%;padding:0.7em 1em;border-radius:var(--radius-sm);border:1.5px solid var(--line);margin-bottom:10px;"></textarea>
            <button type="submit" class="btn btn-danger btn-sm">Signaler à ElevConnect</button>
          </form>
        </div>
      @endif

      @if (! $commande->estRetraitDirect() && $commande->statut === 'livree')
        <div class="dash-card" style="background:var(--sand);box-shadow:none;">
          <h3 style="font-size:1.05rem;margin-bottom:10px;">Confirmer la réception</h3>
          <p class="form-hint" style="margin-bottom:14px;">
            Scannez votre code QR (ci-dessus, sur cette même page) ou saisissez-le
            manuellement pour confirmer que vous avez bien reçu votre commande.
          </p>
          <form method="POST" action="{{ route('commandes.confirmer-reception', $commande) }}" style="display:flex;gap:10px;">
            @csrf
            <input type="text" name="code" placeholder="Code de vérification" required style="flex:1;padding:0.6em 1em;border-radius:var(--radius-sm);border:1.5px solid var(--line);">
            <button type="submit" class="btn btn-primary btn-sm">Confirmer la réception</button>
          </form>

          <button type="button" class="btn btn-ghost-dark btn-sm" id="toggleDispute" style="margin-top:14px;">Signaler un problème</button>
          <form method="POST" action="{{ route('commandes.signaler-probleme', $commande) }}" id="disputeForm" style="display:none;margin-top:12px;">
            @csrf
            <textarea name="description" rows="3" placeholder="Décrivez le problème rencontré" required style="width:100%;padding:0.7em 1em;border-radius:var(--radius-sm);border:1.5px solid var(--line);margin-bottom:10px;"></textarea>
            <button type="submit" class="btn btn-danger btn-sm">Signaler à ElevConnect</button>
          </form>
        </div>
      @endif

      @if ($commande->statut === 'confirmee' && ! $commande->note_client_commande)
        <div class="dash-card" style="background:var(--sand);box-shadow:none;">
          <h3 style="font-size:1.05rem;margin-bottom:10px;">Votre avis</h3>
          <form method="POST" action="{{ route('commandes.noter', $commande) }}">
            @csrf
            <div class="form-field">
              <label>Note du fournisseur (1 à 5)</label>
              <select name="note_client_commande" required>
                @for ($i = 5; $i >= 1; $i--)
                  <option value="{{ $i }}">{{ $i }} {{ $i > 1 ? 'étoiles' : 'étoile' }}</option>
                @endfor
              </select>
            </div>
            <div class="form-field">
              <label>Commentaire (facultatif)</label>
              <textarea name="avis_client_commande" rows="2"></textarea>
            </div>
            @if ($commande->livraison && $commande->livraison->id_livreur)
              <div class="form-field">
                <label>Note du livreur (1 à 5)</label>
                <select name="note_client_livraison">
                  <option value="">—</option>
                  @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}">{{ $i }} {{ $i > 1 ? 'étoiles' : 'étoile' }}</option>
                  @endfor
                </select>
              </div>
              <div class="form-field">
                <label>Commentaire sur la livraison (facultatif)</label>
                <textarea name="avis_client_livraison" rows="2"></textarea>
              </div>
            @endif
            <button type="submit" class="btn btn-primary btn-sm">Envoyer mon avis</button>
          </form>
        </div>
      @endif

      @can('annuler', $commande)
        <form method="POST" action="{{ route('commandes.annuler', $commande) }}" onsubmit="return confirm('Annuler cette commande ?');" style="margin-top:20px;">
          @csrf
          <button type="submit" class="btn btn-danger btn-sm">Annuler la commande</button>
        </form>
      @endcan
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  const toggleBtn = document.getElementById('toggleDispute');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function(){
      const form = document.getElementById('disputeForm');
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });
  }
</script>
@endpush
