@extends('layouts.app')

@section('title', 'Créer un compte — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<section class="auth-section">
  <div class="container">
    <div class="auth-card" style="max-width:720px;">
      <h1>Créer un compte</h1>
      <p class="sub">Inscription différenciée par type d'acteur — chaque compte enregistre une position GPS captée depuis votre téléphone (recherche par proximité).</p>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">
          <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf

        <div class="role-picker" id="rolePicker">
          @foreach ($roles as $r)
            <div style="position:relative;">
              <input type="radio" name="role" id="role-{{ $r }}" value="{{ $r }}" {{ $role === $r ? 'checked' : '' }}>
              <label for="role-{{ $r }}">
                {{ match($r) {
                    'eleveur' => 'Éleveur',
                    'acheteur' => 'Acheteur',
                    'vendeur_provende' => 'Vendeur de provende',
                    'vendeur_accessoire' => 'Vendeur d\'accessoires',
                    'veterinaire' => 'Vétérinaire',
                    'livreur' => 'Livreur',
                    default => ucfirst($r),
                } }}
              </label>
            </div>
          @endforeach
        </div>

        <div class="form-grid">
          <div class="form-field">
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}" required>
          </div>
          <div class="form-field">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required>
          </div>
          <div class="form-field">
            <label for="email">Adresse email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
          </div>
          <div class="form-field">
            <label for="telephone">Téléphone</label>
            <input type="tel" id="telephone" name="telephone" value="{{ old('telephone') }}">
          </div>
          <div class="form-field">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required minlength="8">
          </div>
          <div class="form-field">
            <label for="password_confirmation">Confirmation</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
          </div>
          <div class="form-field full">
            <label for="adresse">Adresse / zone</label>
            <input type="text" id="adresse" name="adresse" value="{{ old('adresse') }}" placeholder="Ex. Parakou, quartier Banikanni">
          </div>

          {{-- Champs spécifiques par rôle --}}
          <div class="form-field role-only" data-for="eleveur">
            <label for="nom_exploitation">Nom de l'exploitation</label>
            <input type="text" id="nom_exploitation" name="nom_exploitation" value="{{ old('nom_exploitation') }}">
          </div>
          <div class="form-field role-only" data-for="vendeur_provende,vendeur_accessoire">
            <label for="nom_boutique">Nom de la boutique</label>
            <input type="text" id="nom_boutique" name="nom_boutique" value="{{ old('nom_boutique') }}">
          </div>
          <div class="form-field role-only" data-for="veterinaire">
            <label for="specialite">Spécialité</label>
            <input type="text" id="specialite" name="specialite" value="{{ old('specialite') }}">
          </div>
          <div class="form-field role-only" data-for="veterinaire">
            <label for="zone_intervention">Zone d'intervention</label>
            <input type="text" id="zone_intervention" name="zone_intervention" value="{{ old('zone_intervention') }}">
          </div>
          <div class="form-field role-only" data-for="livreur">
            <label for="moyen_transport">Moyen de transport</label>
            <input type="text" id="moyen_transport" name="moyen_transport" value="{{ old('moyen_transport') }}" placeholder="Moto, camionnette…">
          </div>
          <div class="form-field role-only" data-for="livreur">
            <label for="zone_couverture">Zone de couverture</label>
            <input type="text" id="zone_couverture" name="zone_couverture" value="{{ old('zone_couverture') }}">
          </div>
          <div class="form-field role-only" data-for="acheteur">
            <label for="type_acheteur">Type d'acheteur</label>
            <select id="type_acheteur" name="type_acheteur">
              <option value="particulier">Particulier</option>
              <option value="professionnel">Professionnel</option>
            </select>
          </div>
        </div>

        <div class="geoloc-status" id="geolocStatus">
          Localisation requise — cliquez sur « Activer ma position » ci-dessous.
        </div>
        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
        <button type="button" class="btn btn-ghost-dark" id="geolocBtn" style="margin-bottom:20px;">
          Activer ma position
        </button>

        <button type="submit" class="btn btn-primary auth-submit" id="submitBtn">Créer mon compte</button>
      </form>

      <p class="auth-switch">Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a></p>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
(function(){
  const roleInputs = document.querySelectorAll('input[name="role"]');
  const roleOnlyFields = document.querySelectorAll('.role-only');

  function applyRoleVisibility(){
    const selected = document.querySelector('input[name="role"]:checked')?.value;
    roleOnlyFields.forEach(field => {
      const roles = field.dataset.for.split(',');
      const show = roles.includes(selected);
      field.style.display = show ? 'flex' : 'none';
      field.querySelectorAll('input, select').forEach(el => el.disabled = !show);
    });
  }
  roleInputs.forEach(r => r.addEventListener('change', applyRoleVisibility));
  applyRoleVisibility();

  // Géolocalisation obligatoire à l'inscription (position GPS captée depuis le
  // téléphone — règle transversale du cahier des charges, chap. 3).
  const geolocBtn = document.getElementById('geolocBtn');
  const geolocStatus = document.getElementById('geolocStatus');
  const latInput = document.getElementById('latitude');
  const lngInput = document.getElementById('longitude');
  const submitBtn = document.getElementById('submitBtn');

  submitBtn.addEventListener('click', function(e){
    if (!latInput.value || !lngInput.value) {
      e.preventDefault();
      geolocStatus.textContent = "Veuillez activer votre position avant de créer votre compte.";
      geolocStatus.className = 'geoloc-status err';
    }
  });

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
      geolocStatus.textContent = "Position activée ✓";
      geolocStatus.className = 'geoloc-status ok';
    }, function(){
      geolocStatus.textContent = "Impossible d'obtenir votre position. Autorisez la géolocalisation puis réessayez.";
      geolocStatus.className = 'geoloc-status err';
    });
  });
})();
</script>
@endpush
