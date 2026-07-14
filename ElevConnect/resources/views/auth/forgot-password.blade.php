@extends('layouts.app')

@section('title', 'Mot de passe oublié — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<section class="auth-section">
  <div class="container">
    <div class="auth-card">
      <h1>Mot de passe oublié</h1>
      <p class="sub">Indiquez votre adresse email : si un compte y est associé, un lien de réinitialisation vous sera envoyé.</p>

      @if (session('status'))
        <div class="geoloc-status ok" style="margin-bottom:16px;">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="form-field">
          <label for="email">Adresse email</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary auth-submit">Envoyer le lien de réinitialisation</button>
      </form>

      <p class="auth-switch"><a href="{{ route('login') }}">← Retour à la connexion</a></p>
    </div>
  </div>
</section>
@endsection
