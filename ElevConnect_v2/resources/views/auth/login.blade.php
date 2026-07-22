@extends('layouts.app')

@section('title', 'Connexion — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<section class="auth-section">
  <div class="container">
    <div class="auth-card">
      <h1>Connexion</h1>
      <p class="sub">Accédez à votre espace ElevConnect.</p>

      @if (session('status'))
        <div class="geoloc-status ok" style="margin-bottom:16px;">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-field">
          <label for="email">Adresse email</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="form-field">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
          <label style="display:flex;align-items:center;gap:8px;font-size:0.85rem;color:var(--ink-soft);">
            <input type="checkbox" name="remember"> Se souvenir de moi
          </label>
          <a href="{{ route('password.request') }}" style="font-size:0.85rem;font-weight:700;color:var(--clay-dark);">Mot de passe oublié ?</a>
        </div>
        <button type="submit" class="btn btn-primary auth-submit">Se connecter</button>
      </form>

      <p class="auth-switch">Pas encore de compte ? <a href="{{ route('register') }}">Créer un compte</a></p>
    </div>
  </div>
</section>
@endsection
