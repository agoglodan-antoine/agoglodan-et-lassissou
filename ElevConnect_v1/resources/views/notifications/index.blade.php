@extends('layouts.mon-espace')

@section('title', 'Notifications — ElevConnect')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<section class="dash-section">
  <div class="container" style="max-width:700px;">
    <div class="dash-head">
      <div>
        <h1>Notifications</h1>
        <p>Suivi de vos annonces, commandes, livraisons et rendez-vous.</p>
      </div>
    </div>

    <div class="dash-card" style="padding:0;">
      @if ($notifications->isEmpty())
        <div class="empty-state">Aucune notification pour le moment.</div>
      @else
        @foreach ($notifications as $notif)
          <div style="padding:18px 20px;border-bottom:1px solid var(--line);{{ ! $notif->lu ? 'background:var(--pasture-soft);' : '' }}">
            <div>{{ $notif->contenu }}</div>
            <div class="form-hint" style="margin-top:4px;">{{ $notif->date_creation->diffForHumans() }}</div>
          </div>
        @endforeach
      @endif
    </div>

    {{ $notifications->links() }}
  </div>
</section>
@endsection
