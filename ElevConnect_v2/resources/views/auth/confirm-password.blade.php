@extends('layouts.app')

@section('title', 'Confirmation du mot de passe — ElevConnect')

@push('styles')
   @vite(['resources/css/auth.css'])
@endpush

@section('content')
<section class="auth-section">
  <div class="container">
    <div class="auth-card">
      <h1>Confirmation du mot de passe</h1>
      <p class="sub">Veuillez confirmer votre mot de passe avant de continuer.</p>

      @if ($errors->any())
        <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="form-field">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" required autofocus>
          <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <button type="submit" class="btn btn-primary auth-submit">Confirmer</button>
      </form>
    </div>
  </div>
</section>
@endsection