@props(['status', 'label' => null, 'force' => null])

@php
  // Classification par défaut d'un statut métier en 3 familles visuelles.
  // Centralise une logique auparavant dupliquée (ternaires / in_array) dans
  // une vingtaine de vues (annonces, commandes, livraisons, services,
  // rendez-vous, versements, utilisateurs...).
  $positifs = ['visible', 'validee', 'confirme', 'confirmee', 'realise', 'terminee', 'actif', 'reussi', 'disponible'];
  $negatifs = ['rejetee', 'annulee', 'annule', 'refusee', 'refuse', 'suspendu', 'expire', 'indisponible', 'en_litige'];

  $classe = $force ?? (
      in_array($status, $positifs, true) ? 'visible'
      : (in_array($status, $negatifs, true) ? 'rejetee' : 'en_attente')
  );

  $texte = $label ?? str_replace('_', ' ', $status);
@endphp

<span {{ $attributes->merge(['class' => 'status-pill '.$classe]) }}>{{ $texte }}</span>
