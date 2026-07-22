@extends('layouts.monEspace')

@section('title', 'Commande '.$commande->code_authenticite.' — ElevConnect')

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:700px;">
    <div class="dash-card">
      <x-back-link :href="route('mon-espace.commandes.index')" label="Retour à Mes achats" />
      <div class="dash-head" style="margin-bottom:20px;">
        <div>
          <h1 style="font-size:1.5rem;">Commande {{ $commande->code_authenticite }}</h1>
          <p>{{ $commande->annonce->titre }}</p>
        </div>
        <x-status-pill :status="$commande->statut" />
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
          <x-status-pill :status="$commande->livraison->statut" />
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
        <div class="dash-card" style="background:var(--sand);box-shadow:none;">
          <p class="form-hint">
            <i class="fa-solid fa-lock"></i> Au moment de la remise, le livreur affichera un code QR sur son
            téléphone : vous n'aurez qu'à le scanner pour confirmer la réception. Aucune action n'est requise
            pour l'instant.
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
          <form method="POST" action="{{ route('mon-espace.commandes.confirmer-reception', $commande) }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">J'ai récupéré ma commande</button>
          </form>

          <button type="button" class="btn btn-ghost-dark btn-sm" id="toggleDispute" style="margin-top:14px;">Signaler un problème</button>
          <form method="POST" action="{{ route('mon-espace.commandes.signaler-probleme', $commande) }}" id="disputeForm" style="display:none;margin-top:12px;">
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
            Le livreur affiche un code QR sur son téléphone au moment de la remise.
            Cliquez ci-dessous puis scannez ce code pour confirmer que la commande vous a bien été livrée.
          </p>

          <form method="POST" action="{{ route('mon-espace.commandes.confirmer-reception', $commande) }}" id="confirmReceptionForm">
            @csrf
            <input type="hidden" name="code" id="codeAuthenticiteInput">
            <button type="button" class="btn btn-primary btn-sm" id="qrScannerOpen"><i class="fa-solid fa-camera"></i> Confirmer la réception</button>
          </form>

          <button type="button" class="btn btn-ghost-dark btn-sm" id="toggleManualCode" style="margin-top:10px;">Je n'arrive pas à scanner, saisir le code manuellement</button>
          <form method="POST" action="{{ route('mon-espace.commandes.confirmer-reception', $commande) }}" id="manualCodeForm" style="display:none;gap:10px;margin-top:10px;">
            @csrf
            <input type="text" name="code" placeholder="Code transmis par le livreur" required style="flex:1;padding:0.6em 1em;border-radius:var(--radius-sm);border:1.5px solid var(--line);">
            <button type="submit" class="btn btn-primary btn-sm">Valider</button>
          </form>

          <div class="qr-modal" id="qrScannerModal" style="display:none;">
            <div class="qr-modal-inner">
              <div class="qr-modal-head">
                <span>Scanner le code du livreur</span>
                <button type="button" id="qrScannerClose" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <video id="qrScannerVideo" playsinline muted></video>
              <canvas id="qrScannerCanvas" style="display:none;"></canvas>
              <p class="form-hint" id="qrScannerHint" style="margin-top:10px;">Placez le QR code affiché par le livreur devant la caméra.</p>
            </div>
          </div>

          <button type="button" class="btn btn-ghost-dark btn-sm" id="toggleDispute" style="margin-top:14px;">Signaler un problème</button>
          <form method="POST" action="{{ route('mon-espace.commandes.signaler-probleme', $commande) }}" id="disputeForm" style="display:none;margin-top:12px;">
            @csrf
            <textarea name="description" rows="3" placeholder="Décrivez le problème rencontré" required style="width:100%;padding:0.7em 1em;border-radius:var(--radius-sm);border:1.5px solid var(--line);margin-bottom:10px;"></textarea>
            <button type="submit" class="btn btn-danger btn-sm">Signaler à ElevConnect</button>
          </form>
        </div>
      @endif

      @if ($commande->statut === 'confirmee' && ! $commande->note_client_commande)
        <div class="dash-card" style="background:var(--sand);box-shadow:none;">
          <h3 style="font-size:1.05rem;margin-bottom:10px;">Votre avis</h3>
          <form method="POST" action="{{ route('mon-espace.commandes.noter', $commande) }}">
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
        <form method="POST" action="{{ route('mon-espace.commandes.annuler', $commande) }}" onsubmit="return confirm('Annuler cette commande ?');" style="margin-top:20px;">
          @csrf
          <button type="submit" class="btn btn-danger btn-sm">Annuler la commande</button>
        </form>
      @endcan
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
  const toggleBtn = document.getElementById('toggleDispute');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function(){
      const form = document.getElementById('disputeForm');
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });
  }

  const toggleManualBtn = document.getElementById('toggleManualCode');
  if (toggleManualBtn) {
    toggleManualBtn.addEventListener('click', function () {
      const form = document.getElementById('manualCodeForm');
      form.style.display = form.style.display === 'none' ? 'flex' : 'none';
    });
  }

  // Scanner de code QR par caméra (API MediaDevices + décodage jsQR) : le
  // clic sur "Confirmer la réception" lance directement la caméra. Le QR
  // scanné est celui affiché par le livreur (jamais celui de l'acheteur
  // lui-même) ; dès qu'un code est détecté, la confirmation est envoyée
  // automatiquement au serveur, qui déchiffre et compare le code réel.
  const qrOpenBtn = document.getElementById('qrScannerOpen');
  if (qrOpenBtn) {
    const qrModal = document.getElementById('qrScannerModal');
    const qrCloseBtn = document.getElementById('qrScannerClose');
    const qrVideo = document.getElementById('qrScannerVideo');
    const qrCanvas = document.getElementById('qrScannerCanvas');
    const qrCtx = qrCanvas.getContext('2d', { willReadFrequently: true });
    const qrHint = document.getElementById('qrScannerHint');
    const codeInput = document.getElementById('codeAuthenticiteInput');
    const confirmForm = document.getElementById('confirmReceptionForm');

    let qrStream = null;
    let qrScanning = false;

    function stopQrScanner() {
      qrScanning = false;
      if (qrStream) {
        qrStream.getTracks().forEach(function (track) { track.stop(); });
        qrStream = null;
      }
      qrModal.style.display = 'none';
    }

    function scanQrFrame() {
      if (!qrScanning) return;

      if (qrVideo.readyState === qrVideo.HAVE_ENOUGH_DATA) {
        qrCanvas.width = qrVideo.videoWidth;
        qrCanvas.height = qrVideo.videoHeight;
        qrCtx.drawImage(qrVideo, 0, 0, qrCanvas.width, qrCanvas.height);

        const imageData = qrCtx.getImageData(0, 0, qrCanvas.width, qrCanvas.height);
        const result = jsQR(imageData.data, imageData.width, imageData.height);

        if (result && result.data) {
          codeInput.value = result.data;
          qrHint.textContent = 'Code détecté, confirmation en cours…';
          setTimeout(function () {
            stopQrScanner();
            confirmForm.submit();
          }, 400);
          return;
        }
      }

      requestAnimationFrame(scanQrFrame);
    }

    qrOpenBtn.addEventListener('click', async function () {
      qrModal.style.display = 'flex';

      if (typeof jsQR === 'undefined') {
        qrHint.textContent = "Le scanner n'a pas pu se charger. Utilisez la saisie manuelle ci-dessous.";
        return;
      }

      qrHint.textContent = 'Placez le QR code affiché par le livreur devant la caméra.';

      try {
        qrStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        qrVideo.srcObject = qrStream;
        await qrVideo.play();
        qrScanning = true;
        requestAnimationFrame(scanQrFrame);
      } catch (e) {
        qrHint.textContent = "Impossible d'accéder à la caméra. Vérifiez les autorisations, ou utilisez la saisie manuelle ci-dessous.";
      }
    });

    qrCloseBtn.addEventListener('click', stopQrScanner);
  }
</script>
@endpush
