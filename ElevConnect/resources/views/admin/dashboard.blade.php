@extends('layouts.app')

@section('title', 'Administration — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container">
    <div class="dash-head">
      <div>
        <h1>Vue d'ensemble</h1>
        <p>Indicateurs clés de la plateforme.</p>
      </div>
    </div>

    @include('admin._nav')

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
      <div class="dash-card">
        <div class="form-hint">Annonces en attente</div>
        <div style="font-size:2rem;font-weight:800;">{{ $stats['annonces_en_attente'] }}</div>
        <a href="{{ route('admin.moderation.index') }}" style="font-size:0.85rem;font-weight:700;color:var(--clay-dark);">Modérer →</a>
      </div>
      <div class="dash-card">
        <div class="form-hint">Litiges ouverts</div>
        <div style="font-size:2rem;font-weight:800;">{{ $stats['litiges_ouverts'] }}</div>
        <a href="{{ route('admin.litiges.index') }}" style="font-size:0.85rem;font-weight:700;color:var(--clay-dark);">Traiter →</a>
      </div>
      <div class="dash-card">
        <div class="form-hint">Utilisateurs actifs</div>
        <div style="font-size:2rem;font-weight:800;">{{ $stats['utilisateurs_actifs'] }}</div>
      </div>
      <div class="dash-card">
        <div class="form-hint">Comptes suspendus</div>
        <div style="font-size:2rem;font-weight:800;">{{ $stats['utilisateurs_suspendus'] }}</div>
      </div>
      <div class="dash-card">
        <div class="form-hint">Commandes confirmées</div>
        <div style="font-size:2rem;font-weight:800;">{{ $stats['commandes_confirmees'] }}</div>
      </div>
    </div>
  </div>
</section>
@endsection
