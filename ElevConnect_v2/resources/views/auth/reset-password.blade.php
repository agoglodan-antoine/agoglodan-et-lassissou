@extends('layouts.app')

@section('title', 'Réinitialiser le mot de passe — ElevConnect')

@push('styles')
   @vite(['resources/css/auth.css'])
@endpush

@section('content')
<section class="auth-section">
  <div class="container">
    <div class="auth-card">
      <h1>Nouveau mot de passe</h1>
      <p class="sub">Choisissez un nouveau mot de passe pour votre compte ElevConnect.</p>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-field">
          <label for="email">Adresse email</label>
          <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus>
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
        <button type="submit" class="btn btn-primary auth-submit">Réinitialiser le mot de passe</button>
      </form>
    </div>
  </div>
</section>
@endsection
