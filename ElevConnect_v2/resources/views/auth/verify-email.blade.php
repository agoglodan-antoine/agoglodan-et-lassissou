@extends('layouts.app')

@section('title', 'Vérification email — ElevConnect')

@push('styles')
   @vite(['resources/css/auth.css'])
@endpush

@section('content')
<section class="auth-section">
  <div class="container">
    <div class="auth-card">
      <h1>Vérification de l'email</h1>
      <p class="sub">
        Merci pour votre inscription ! Avant de commencer, veuillez vérifier votre adresse email 
        en cliquant sur le lien que nous venons de vous envoyer.
      </p>

      @if (session('status') == 'verification-link-sent')
        <div class="geoloc-status ok" style="margin-bottom:16px;">
          Un nouveau lien de vérification a été envoyé à votre adresse email.
        </div>
      @endif

      <div style="display:flex;flex-direction:column;gap:12px;">
        <form method="POST" action="{{ route('verification.send') }}">
          @csrf
          <button type="submit" class="btn btn-primary auth-submit" style="width:100%;">
            Renvoyer l'email de vérification
          </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-secondary auth-submit" style="width:100%;">
            Se déconnecter
          </button>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection