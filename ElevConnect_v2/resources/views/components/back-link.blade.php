@props(['href', 'label' => 'Retour'])

{{-- Bouton de retour générique, en tête des vues create/edit/show.
     Usage : <x-back-link :href="route('mon-espace.annonces.index')" label="Retour à Mon catalogue" /> --}}
<a {{ $attributes->merge(['class' => 'back-link']) }} href="{{ $href }}">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
  {{ $label }}
</a>
