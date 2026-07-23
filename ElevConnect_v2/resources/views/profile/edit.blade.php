@extends('layouts.monEspace')

@section('title', 'Mon profil — ElevConnect')

@push('styles')
   @vite(['resources/css/auth.css'])
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <x-back-link :href="route('mon-espace.dashboard')" label="Retour au tableau de bord" />
    <div class="dash-head">
      <div>
        <h1>Mon profil</h1>
        <p>Modifiez vos informations personnelles et votre position.</p>
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

    <div class="auth-card" style="max-width:640px;">
      <form method="POST" action="{{ route('mon-espace.profile.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="form-field" style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
          <img
            id="photoProfilPreview"
            src="{{ $user->photo_profil ? asset('storage/'.$user->photo_profil) : asset('images/avatar-placeholder.svg') }}"
            alt="Photo de profil"
            style="width:72px;height:72px;border-radius:50%;object-fit:cover;background:var(--sand);border:1px solid var(--line);"
          >
          <div>
            <label for="photo_profil" style="display:block;margin-bottom:4px;">Photo de profil</label>
            <input type="file" id="photo_profil" name="photo_profil" accept="image/*" data-max-mb="4">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-field">
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" value="{{ old('prenom', $user->prenom) }}" required>
          </div>
          <div class="form-field">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" value="{{ old('nom', $user->nom) }}" required>
          </div>
          <div class="form-field">
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
          </div>
          <div class="form-field">
            <label for="telephone">Téléphone</label>
            <input type="tel" id="telephone" name="telephone" value="{{ old('telephone', $user->telephone) }}">
          </div>
          <div class="form-field full">
            <label for="adresse">Adresse / zone</label>
            <input type="text" id="adresse" name="adresse" value="{{ old('adresse', $user->adresse) }}">
          </div>

          {{-- Champs propres au rôle --}}
          @if ($user->role === 'eleveur')
            <div class="form-field full">
              <label for="nom_exploitation">Nom de l'exploitation</label>
              <input type="text" id="nom_exploitation" name="nom_exploitation" value="{{ old('nom_exploitation', $profil->nom_exploitation ?? '') }}">
            </div>
          @endif

          @if (in_array($user->role, ['vendeur_provende', 'vendeur_accessoire']))
            <div class="form-field full">
              <label for="nom_boutique">Nom de la boutique</label>
              <input type="text" id="nom_boutique" name="nom_boutique" value="{{ old('nom_boutique', $profil->nom_boutique ?? '') }}">
            </div>
          @endif

          @if ($user->role === 'veterinaire')
            <div class="form-field">
              <label for="specialite">Spécialité</label>
              <input type="text" id="specialite" name="specialite" value="{{ old('specialite', $profil->specialite ?? '') }}">
            </div>
            <div class="form-field">
              <label for="zone_intervention">Zone d'intervention</label>
              <input type="text" id="zone_intervention" name="zone_intervention" value="{{ old('zone_intervention', $profil->zone_intervention ?? '') }}">
            </div>
          @endif

          @if ($user->role === 'livreur')
            <div class="form-field">
              <label for="moyen_transport">Moyen de transport</label>
              <input type="text" id="moyen_transport" name="moyen_transport" value="{{ old('moyen_transport', $profil->moyen_transport ?? '') }}">
            </div>
            <div class="form-field">
              <label for="zone_couverture">Zone de couverture</label>
              <input type="text" id="zone_couverture" name="zone_couverture" value="{{ old('zone_couverture', $profil->zone_couverture ?? '') }}">
            </div>
          @endif

          @if ($user->role === 'acheteur')
            <div class="form-field">
              <label for="type_acheteur">Type d'acheteur</label>
              <select id="type_acheteur" name="type_acheteur">
                <option value="particulier" {{ old('type_acheteur', $profil->type_acheteur ?? '') === 'particulier' ? 'selected' : '' }}>Particulier</option>
                <option value="professionnel" {{ old('type_acheteur', $profil->type_acheteur ?? '') === 'professionnel' ? 'selected' : '' }}>Professionnel</option>
              </select>
            </div>
          @endif
        </div>

        <div class="geoloc-status {{ $user->latitude ? 'ok' : '' }}" id="geolocStatus" style="margin-top:6px;">
          @if ($user->latitude && $user->longitude)
            <i class="fa-solid fa-check"></i> Position actuelle enregistrée — utilisée pour la recherche par proximité.
          @else
            Aucune position enregistrée pour le moment.
          @endif
        </div>
        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $user->latitude) }}">
        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $user->longitude) }}">
        <button type="button" class="btn btn-ghost-dark" id="geolocBtn" style="margin:12px 0 20px;">
          Mettre à jour ma position
        </button>

        <button type="submit" class="btn btn-primary auth-submit">Enregistrer les modifications</button>
      </form>
    </div>

    <div class="auth-card" style="max-width:640px;margin-top:24px;">
      <h3 style="font-size:1.1rem;margin-bottom:16px;">Changer de mot de passe</h3>
      <form method="POST" action="{{ route('mon-espace.profile.password') }}">
        @csrf @method('PUT')
        <div class="form-field">
          <label for="password_actuel">Mot de passe actuel</label>
          <input type="password" id="password_actuel" name="password_actuel" required>
        </div>
        <div class="form-field">
          <label for="password">Nouveau mot de passe</label>
          <input type="password" id="password" name="password" required minlength="8">
          <span class="form-hint">Au moins 8 caractères, avec une lettre et un chiffre.</span>
        </div>
        <div class="form-field">
          <label for="password_confirmation">Confirmation</label>
          <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary auth-submit">Changer le mot de passe</button>
      </form>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
(function(){
  const photoInput = document.getElementById('photo_profil');
  const photoPreview = document.getElementById('photoProfilPreview');
  if (photoInput && photoPreview) {
    photoInput.addEventListener('change', function () {
      if (photoInput.files && photoInput.files[0]) {
        photoPreview.src = URL.createObjectURL(photoInput.files[0]);
      }
    });
  }

  const geolocBtn = document.getElementById('geolocBtn');
  const geolocStatus = document.getElementById('geolocStatus');
  const latInput = document.getElementById('latitude');
  const lngInput = document.getElementById('longitude');

  geolocBtn.addEventListener('click', function(){
    if (!navigator.geolocation) {
      geolocStatus.textContent = "La géolocalisation n'est pas disponible sur cet appareil.";
      geolocStatus.className = 'geoloc-status err';
      return;
    }
    geolocStatus.textContent = "Localisation en cours…";
    geolocStatus.className = 'geoloc-status';
    navigator.geolocation.getCurrentPosition(function(pos){
      latInput.value = pos.coords.latitude;
      lngInput.value = pos.coords.longitude;
      geolocStatus.innerHTML = "<i class=\"fa-solid fa-check\"></i> Nouvelle position captée — enregistrez pour la sauvegarder.";
      geolocStatus.className = 'geoloc-status ok';
    }, function(){
      geolocStatus.textContent = "Impossible d'obtenir votre position. Autorisez la géolocalisation puis réessayez.";
      geolocStatus.className = 'geoloc-status err';
    });
  });
})();
</script>
@endpush
