{{-- Bouton de retour générique, utilisé en tête des vues create/edit/show.
     Usage : @include('partials.back-link', ['href' => route('mon-espace.annonces.index'), 'label' => 'Retour à Mon catalogue']) --}}
<a href="{{ $href }}" class="back-link">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
  {{ $label ?? 'Retour' }}
</a>
