@extends('layouts.app')

@section('content')

{{--
  ============================================================================
  HERO — repris de Maquette_accueil.html (structure, styles, animations
  radar/compteurs à l'identique). Le texte a été corrigé pour rester fidèle
  au cahier des charges du mémoire (voir README_ROADMAP.md) : la plateforme
  prélève une commission transparente de 5 % sur les ventes ET les
  livraisons effectivement réalisées — il n'y a pas de "commission 0 %".
  Le champ de recherche du hero a été retiré au profit d'une recherche
  générale accessible depuis la barre de navigation (voir SearchController).
  ============================================================================
--}}
<section class="hero" id="top">
  <div class="container hero-grid">
    <div class="hero-copy">
      <span class="eyebrow hero-eyebrow">Marketplace agricole béninoise</span>
      <h1>Le bétail de votre région,<br><em>à portée de main.</em></h1>
      <p class="lead">ElevConnect met en relation éleveurs, vendeurs de provende, vendeurs d'accessoires, acheteurs, vétérinaires et livreurs partout au Bénin — recherche par proximité, rendez-vous vétérinaires, paiement sécurisé en séquestre et traçabilité « Made in Bénin » garantie par code QR à chaque livraison.</p>

      <div class="hero-cta">
        <a href="{{ route('catalogue.index') }}" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="white" stroke-width="2"/><path d="M20 20l-4-4" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
          Parcourir les annonces
        </a>
        @guest
          <a href="{{ route('register') }}" class="btn btn-outline-cream">Créer un compte gratuit</a>
        @endguest
      </div>

      <div class="hero-stats">
        <div class="hero-stat"><b id="statEleveurs" data-target="{{ $stats['eleveurs'] }}" data-suffix="+">0</b><span>Éleveurs inscrits</span></div>
        <div class="hero-stat"><b id="statCommunes" data-target="{{ $stats['communes'] }}">0</b><span>Communes couvertes</span></div>
        <div class="hero-stat"><b id="statAnnonces" data-target="{{ $stats['annonces'] }}" data-suffix="+">0</b><span>Annonces publiées</span></div>
      </div>
    </div>

    <div class="radar-wrap">
      <div class="radar">
        <div class="radar-sweep"></div>
        <div class="radar-ring r1"></div>
        <div class="radar-ring r2"></div>
        <div class="radar-ring r3"></div>
        <div class="radar-center">
          <div class="radar-pulse"></div>
          <svg viewBox="0 0 24 24"><path d="M12 2C8 2 5 5.2 5 9.2c0 5.6 7 12.8 7 12.8s7-7.2 7-12.8C19 5.2 16 2 12 2zm0 9.6a2.6 2.6 0 110-5.2 2.6 2.6 0 010 5.2z"/></svg>
        </div>
      </div>
      <div class="radar-pin pin-1">
        <span class="dot"><svg viewBox="0 0 24 24"><path d="M4 16c0-5 3-9 8-9s8 4 8 9"/></svg></span>
        Vétérinaire <span>3 km</span>
      </div>
      <div class="radar-pin pin-2">
        <span class="dot"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/></svg></span>
        Éleveur <span>5,1 km</span>
      </div>
      <div class="radar-pin pin-3">
        <span class="dot"><svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3"/></svg></span>
        Livreur <span>7 km</span>
      </div>
      <div class="float-card">
        <span class="fc-tag">Made in Bénin</span>
        <b>Taurillon Borgou</b>
        <small>Parakou · 2,4 km · 350 000 FCFA</small>
      </div>
    </div>
  </div>
</section>

{{-- TRUST STRIP — corrigé : la commission de 5% est réelle, il n'y a pas de messagerie interne. --}}
<div class="trust-strip">
  <div class="trust-track">
    <span>Paiement sécurisé en séquestre</span>
    <span>Commission transparente de 5 % sur les ventes réalisées</span>
    <span>Vérification par code QR à la livraison</span>
    <span>Vétérinaires vérifiés</span>
    <span>Traçabilité « Made in Bénin »</span>
    <span>Géolocalisation par proximité</span>
    <span>Paiement sécurisé en séquestre</span>
    <span>Commission transparente de 5 % sur les ventes réalisées</span>
    <span>Vérification par code QR à la livraison</span>
    <span>Vétérinaires vérifiés</span>
    <span>Traçabilité « Made in Bénin »</span>
    <span>Géolocalisation par proximité</span>
  </div>
</div>

{{-- SPECIES --}}
<section class="section" id="especes">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Catalogue</span>
      <h2>Parcourez par espèce</h2>
      <p>Chaque annonce est rattachée à une race précise, pour une recherche fine et fiable.</p>
    </div>
    <div class="species-grid">
      <div class="species-card">
        <div class="species-icon"><svg viewBox="0 0 24 24"><path d="M4 15c-1.5-1-2-3 0-4 .3-2 2-3 4-2.6C9 6.7 11 6 12 7c1-1 3-.3 4 1.4 2-.4 3.7.6 4 2.6 2 1 1.5 3 0 4-.3 2.6-2.5 4-5 4H9c-2.5 0-4.7-1.4-5-4z"/></svg></div>
        <h4>Bovins</h4><p>Borgou, Zébu, Lagunaire…</p>
      </div>
      <div class="species-card">
        <div class="species-icon"><svg viewBox="0 0 24 24"><path d="M6 10c-2-2-2-5 0-6 1 2 2 2.8 3 3 1.5-1.5 4-1.5 5 0 1-.2 2-1 3-3 2 1 2 4 0 6 1 3 0 6-3 8v2H9v-2c-3-2-4-5-3-8z"/></svg></div>
        <h4>Caprins</h4><p>Chèvre rousse, Sahélienne…</p>
      </div>
      <div class="species-card">
        <div class="species-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="13" r="7"/><circle cx="7" cy="8" r="2"/><circle cx="17" cy="8" r="2"/><circle cx="12" cy="6" r="2"/></svg></div>
        <h4>Ovins</h4><p>Djallonké, Bali-Bali…</p>
      </div>
      <div class="species-card">
        <div class="species-icon"><svg viewBox="0 0 24 24"><path d="M9 21V13a5 5 0 0110 0c0 3-2 4-2 6h-2M13 5a3 3 0 016 0"/></svg></div>
        <h4>Volailles</h4><p>Poulet local, Pintade…</p>
      </div>
      <div class="species-card">
        <div class="species-icon"><svg viewBox="0 0 24 24"><path d="M5 13a6 6 0 0112 0v1h1.5a1.5 1.5 0 010 3H17a4 4 0 01-4 3H9a4 4 0 01-4-3z"/></svg></div>
        <h4>Porcins</h4><p>Large White, races locales…</p>
      </div>
    </div>
  </div>
</section>

{{-- HOW IT WORKS — corrigé : pas de "messagerie intégrée" (aucune messagerie interne selon le
     cahier des charges), pas de "sans commission" (commission de 5% sur les ventes réalisées). --}}
<section class="section steps-section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Le parcours</span>
      <h2>Comment ça marche</h2>
      <p>Un processus pensé pour être simple depuis un téléphone, du premier contact à la livraison.</p>
    </div>
    <div class="steps-grid">
      <div class="step-card">
        <div class="step-num">01</div>
        <h4>Créez votre profil</h4>
        <p>Éleveur, vendeur de provende, vendeur d'accessoires, acheteur, vétérinaire ou livreur : inscrivez-vous et activez votre position pour être trouvé à proximité.</p>
      </div>
      <div class="step-card">
        <div class="step-num">02</div>
        <h4>Publiez ou parcourez</h4>
        <p>Le fournisseur publie son annonce, soumise à l'approbation d'un administrateur ; l'acheteur filtre par espèce, race, prix et distance.</p>
      </div>
      <div class="step-card">
        <div class="step-num">03</div>
        <h4>Commandez en confiance</h4>
        <p>Paiement en ligne sécurisé, fonds détenus en séquestre par ElevConnect, avis vérifiés et rendez-vous vétérinaires pris directement depuis la plateforme.</p>
      </div>
      <div class="step-card">
        <div class="step-num">04</div>
        <h4>Livraison &amp; versement</h4>
        <p>Un livreur partenaire achemine la commande ; le code QR scanné à la réception déclenche le versement au fournisseur, déduction faite de la commission ElevConnect de 5 %.</p>
      </div>
    </div>
  </div>
</section>

{{-- LISTINGS — dynamiques (annonces réellement "visible" en base), badges corrigés :
     plus de mention "Éleveur Premium/Basique" (l'abonnement est réservé aux vétérinaires). --}}
<section class="section" id="annonces">
  <div class="container">
    <div class="listings-head">
      <div class="section-head" style="margin-bottom:0;">
        <span class="eyebrow">À la une</span>
        <h2>Annonces récentes</h2>
        <a href="{{ route('catalogue.index') }}" style="font-weight:700;color:var(--clay-dark);">Voir toutes les annonces →</a>
      </div>
      <div class="carousel-controls">
        <button id="scrollLeft" aria-label="Précédent"><svg viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6" fill="none" stroke="#1E3626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
        <button id="scrollRight" aria-label="Suivant"><svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6" fill="none" stroke="#1E3626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
      </div>
    </div>

    <div class="listings-track" id="listingsTrack">
      @forelse ($annonces as $annonce)
        <article class="listing-card">
          <a href="{{ route('catalogue.show', $annonce) }}" style="text-decoration:none;color:inherit;display:block;">
            <div class="listing-media">
              <span class="listing-badge {{ $annonce->type_annonce === 'animal' ? 'made' : '' }}">
                {{ ucfirst($annonce->type_annonce) }}
              </span>
              @if ($annonce->image_1)
                <img src="{{ asset('storage/'.$annonce->image_1) }}" alt="{{ $annonce->titre }}" style="width:100%;height:100%;object-fit:cover;">
              @else
                <svg viewBox="0 0 24 24"><path d="M4 15c-1.5-1-2-3 0-4 .3-2 2-3 4-2.6C9 6.7 11 6 12 7c1-1 3-.3 4 1.4 2-.4 3.7.6 4 2.6 2 1 1.5 3 0 4-.3 2.6-2.5 4-5 4H9c-2.5 0-4.7-1.4-5-4z"/></svg>
              @endif
            </div>
          </a>
          <div class="listing-body">
            <a href="{{ route('catalogue.show', $annonce) }}" style="text-decoration:none;color:inherit;">
              <h4>{{ $annonce->titre }}</h4>
            </a>
            <div class="listing-meta">{{ $annonce->auteur->adresse ?? 'Localisation non renseignée' }}</div>
            <div class="listing-price">
              <b>{{ number_format($annonce->prix_unitaire, 0, ',', ' ') }} FCFA</b>
              <span>{{ $annonce->auteur->nom ?? '' }} {{ $annonce->auteur->prenom ?? '' }}</span>
            </div>
            @if (! auth()->check() || auth()->id() !== $annonce->id_utilisateur)
              <a href="{{ route('commandes.create', $annonce) }}" class="btn btn-primary btn-sm" style="width:100%;text-align:center;justify-content:center;margin-top:12px;">Commander</a>
            @endif
          </div>
        </article>
      @empty
        <article class="listing-card">
          <div class="listing-body">
            <h4>Aucune annonce publiée pour le moment</h4>
            <p class="listing-meta">Les annonces apparaîtront ici dès qu'un administrateur les aura approuvées.</p>
          </div>
        </article>
      @endforelse
    </div>
  </div>
</section>

{{-- ROLES — les sept acteurs identifiés au chapitre 3 du mémoire. L'Administrateur
     n'a pas d'onglet public (compte non ouvert à l'auto-inscription, voir RegisterController). --}}
<section class="section" style="background:var(--sand);" id="acteurs">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Pour chaque acteur</span>
      <h2>Une plateforme, six métiers</h2>
      <p>Chaque profil dispose d'un tableau de bord et d'outils dédiés à son rôle.</p>
    </div>
    <div class="roles-tabs" role="tablist">
      <button class="role-tab active" data-role="eleveur">Éleveur</button>
      <button class="role-tab" data-role="provende">Vendeur de provende</button>
      <button class="role-tab" data-role="accessoire">Vendeur d'accessoires</button>
      <button class="role-tab" data-role="acheteur">Acheteur</button>
      <button class="role-tab" data-role="veterinaire">Vétérinaire</button>
      <button class="role-tab" data-role="livreur">Livreur</button>
    </div>

    <div class="roles-panel active" id="panel-eleveur">
      <div class="role-panel-inner">
        <div class="role-visual"><svg viewBox="0 0 24 24"><path d="M4 15c-1.5-1-2-3 0-4 .3-2 2-3 4-2.6C9 6.7 11 6 12 7c1-1 3-.3 4 1.4 2-.4 3.7.6 4 2.6 2 1 1.5 3 0 4-.3 2.6-2.5 4-5 4H9c-2.5 0-4.7-1.4-5-4z"/></svg></div>
        <div>
          <h3>Éleveur</h3>
          <p class="role-desc">Gérez votre cheptel, publiez vos annonces et développez vos ventes, moyennant une commission de 5 % uniquement sur les ventes réalisées.</p>
          <ul class="role-features">
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Publication, modification et archivage d'annonces d'animaux</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Prise de rendez-vous vétérinaire directement en ligne</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Versement automatique après confirmation de réception</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Publication d'actualités pour valoriser le local</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="roles-panel" id="panel-provende">
      <div class="role-panel-inner">
        <div class="role-visual"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M8 8h8v8H8z"/></svg></div>
        <div>
          <h3>Vendeur de provende</h3>
          <p class="role-desc">Vendez provendes et nourriture d'élevage, avec unité de mesure et barème de réduction par quantité.</p>
          <ul class="role-features">
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Annonces avec unité de mesure (sac, kg, litre, bassine…)</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Barème de réduction par tranche de quantité</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Suivi des commandes et statistiques de vente</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="roles-panel" id="panel-accessoire">
      <div class="role-panel-inner">
        <div class="role-visual"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><circle cx="12" cy="12" r="3"/></svg></div>
        <div>
          <h3>Vendeur d'accessoires</h3>
          <p class="role-desc">Vendez outils et accessoires d'élevage, avec barème de réduction par quantité.</p>
          <ul class="role-features">
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Publication et gestion d'annonces d'accessoires</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Barème de réduction par tranche de quantité</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Statistiques de vente</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="roles-panel" id="panel-acheteur">
      <div class="role-panel-inner">
        <div class="role-visual"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg></div>
        <div>
          <h3>Acheteur</h3>
          <p class="role-desc">Trouvez l'animal, la provende ou l'accessoire qu'il vous faut, près de chez vous, en toute confiance.</p>
          <ul class="role-features">
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Recherche par type, localisation, prix et disponibilité</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Paiement en ligne sécurisé, fonds détenus en séquestre</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Choix du livreur et suivi de la livraison</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Confirmation par scan du code QR à réception</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="roles-panel" id="panel-veterinaire">
      <div class="role-panel-inner">
        <div class="role-visual"><svg viewBox="0 0 24 24"><path d="M4 16c0-5 3-9 8-9s8 4 8 9"/><circle cx="12" cy="18" r="2"/></svg></div>
        <div>
          <h3>Vétérinaire</h3>
          <p class="role-desc">Développez votre patientèle et gérez votre agenda, avec un abonnement Basique gratuit ou Premium à 2 000 FCFA/mois.</p>
          <ul class="role-features">
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Agenda de rendez-vous centralisé</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Définition de la zone d'intervention</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Visibilité auprès des éleveurs proches</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Recueil d'avis pour bâtir sa réputation</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="roles-panel" id="panel-livreur">
      <div class="role-panel-inner">
        <div class="role-visual"><svg viewBox="0 0 24 24"><rect x="3" y="9" width="12" height="8" rx="1.5"/><path d="M15 12h3l3 3v2h-6z"/><circle cx="7" cy="19" r="1.6"/><circle cx="17" cy="19" r="1.6"/></svg></div>
        <div>
          <h3>Livreur</h3>
          <p class="role-desc">Assurez le transport sécurisé des produits et développez votre activité de livraison.</p>
          <ul class="role-features">
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Réception de courses de livraison à proximité</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Définition de sa zone de couverture et de son planning</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Affichage du code QR à la remise, pour vérification</li>
            <li><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Évaluation par les acheteurs</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- PRICING — corrigé : l'abonnement est réservé aux VÉTÉRINAIRES uniquement
     (cahier des charges, chap. 2), pas aux éleveurs. Formule Basique gratuite,
     Premium à 2 000 FCFA/mois (et non 5 000 FCFA comme dans la maquette d'origine). --}}
<section class="section" id="abonnements">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Abonnement vétérinaire</span>
      <h2>Réservé aux professionnels de la santé animale</h2>
      <p>Seuls les vétérinaires peuvent souscrire à un abonnement sur ElevConnect ; aucun autre rôle n'y est éligible.</p>
    </div>
    <div class="pricing-grid">
      <div class="price-card">
        <h3>Basique</h3>
        <p class="price-desc" style="color:var(--ink-soft);margin-top:6px;">Pour démarrer sereinement</p>
        <div class="price-amount">Gratuit</div>
        <ul class="role-features" style="margin-top:24px;">
          <li><svg viewBox="0 0 24 24" fill="none" stroke="#BD4A1E"><path d="M20 6L9 17l-5-5" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Services vétérinaires limités</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="#BD4A1E"><path d="M20 6L9 17l-5-5" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Visibilité standard</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="#BD4A1E"><path d="M20 6L9 17l-5-5" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Gestion des rendez-vous</li>
        </ul>
        <a href="{{ route('register') }}?role=veterinaire" class="btn btn-ghost-dark">Démarrer gratuitement</a>
      </div>
      <div class="price-card premium">
        <span class="plan-badge">Recommandé</span>
        <h3>Premium</h3>
        <p class="price-desc">Pour les vétérinaires qui veulent grandir</p>
        <div class="price-amount">2 000 FCFA<sub>/mois</sub></div>
        <ul class="role-features" style="margin-top:24px;">
          <li><svg viewBox="0 0 24 24" fill="none" stroke="#D79B2A"><path d="M20 6L9 17l-5-5" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Services illimités</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="#D79B2A"><path d="M20 6L9 17l-5-5" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Mise en avant prioritaire dans les résultats</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="#D79B2A"><path d="M20 6L9 17l-5-5" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Statistiques détaillées</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="#D79B2A"><path d="M20 6L9 17l-5-5" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Support prioritaire</li>
        </ul>
        <a href="{{ route('register') }}?role=veterinaire" class="btn btn-primary">Passer en Premium</a>
      </div>
    </div>
  </div>
</section>

{{-- TRACEABILITY — corrigé : suppression de la mention "messagerie", accent mis sur le
     séquestre + QR code, conformément à la règle "pas de messagerie interne". --}}
<section class="section trace-section">
  <div class="container trace-grid">
    <div class="trace-visual">
      <div class="trace-badge-big">
        <b>100%</b>
        <span>MADE IN BÉNIN</span>
      </div>
    </div>
    <div>
      <span class="eyebrow">Confiance & traçabilité</span>
      <h2 style="margin-top:10px;">Une approche phygitale, pensée pour le terrain</h2>
      <div class="trace-points">
        <div class="trace-point">
          <div class="trace-icon"><svg viewBox="0 0 24 24"><path d="M12 2C8 2 5 5.2 5 9.2c0 5.6 7 12.8 7 12.8s7-7.2 7-12.8C19 5.2 16 2 12 2z"/></svg></div>
          <div><h4>Origine garantie</h4><p>Chaque animal est rattaché à un fournisseur identifié et localisé, pour une traçabilité complète « Made in Bénin ».</p></div>
        </div>
        <div class="trace-point">
          <div class="trace-icon"><svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 8h2v2H8zM14 8h2v2h-2zM8 14h2v2H8z"/></svg></div>
          <div><h4>Séquestre &amp; code QR</h4><p>Le paiement est détenu en séquestre par ElevConnect et libéré au fournisseur après vérification du code QR par l'acheteur à la réception.</p></div>
        </div>
        <div class="trace-point">
          <div class="trace-icon"><svg viewBox="0 0 24 24"><path d="M12 2l3 7h7l-5.5 4.3L18.5 21 12 16.5 5.5 21l2-7.7L2 9h7z"/></svg></div>
          <div><h4>Avis vérifiés</h4><p>Chaque commande, livraison ou rendez-vous peut faire l'objet d'un avis, pour une communauté fondée sur la confiance.</p></div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- TESTIMONIALS — corrigé : suppression de la mention "sans commission". --}}
<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Témoignages</span>
      <h2>Ils utilisent ElevConnect</h2>
    </div>
    <div class="testi-grid">
      <div class="testi-card">
        <div class="testi-stars">★★★★★</div>
        <p>« Je publie mes annonces en quelques minutes depuis mon téléphone, et je suis payé directement une fois la livraison confirmée. »</p>
        <div class="testi-who">
          <div class="testi-avatar">A.K.</div>
          <div><b>Antoine K.</b><span>Éleveur, Parakou</span></div>
        </div>
      </div>
      <div class="testi-card">
        <div class="testi-stars">★★★★★</div>
        <p>« La recherche par proximité m'a permis de trouver un troupeau de qualité à 6 km de chez moi, avec un livreur fiable en prime. »</p>
        <div class="testi-who">
          <div class="testi-avatar">R.S.</div>
          <div><b>Rachidatou S.</b><span>Acheteuse, Cotonou</span></div>
        </div>
      </div>
      <div class="testi-card">
        <div class="testi-stars">★★★★★</div>
        <p>« Mon agenda de rendez-vous est enfin centralisé et les éleveurs proches me trouvent directement grâce à la plateforme. »</p>
        <div class="testi-who">
          <div class="testi-avatar">Dr.B</div>
          <div><b>Dr Boniface A.</b><span>Vétérinaire, Djougou</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ACTUALITÉS — dynamiques (ACTUALITES en base), publiables par tout rôle
     sauf Acheteur (Utilisateur::peutPublierActualite()). --}}
<section class="section" style="background:var(--sand);" id="actualites">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Actualités</span>
      <h2>Conseils &amp; consommation locale</h2>
      <a href="{{ route('actualites.index') }}" style="font-weight:700;color:var(--clay-dark);">Voir toutes les actualités →</a>
    </div>
    <div class="news-grid">
      @forelse ($actualites as $actualite)
        <article class="news-card">
          @if ($actualite->medias->isNotEmpty())
            <div class="news-media" style="background-image:url('{{ asset('storage/'.$actualite->medias->first()->chemin_fichier) }}');background-size:cover;background-position:center;"></div>
          @else
            <div class="news-media"></div>
          @endif
          <div class="news-body">
            <span class="news-tag">{{ ucfirst(str_replace('_', ' ', $actualite->auteur->role)) }}</span>
            <h4>{{ $actualite->titre }}</h4>
            <p>{{ \Illuminate\Support\Str::limit($actualite->contenu, 110) }}</p>
            <a class="news-link" href="{{ route('actualites.show', $actualite) }}">Lire l'article →</a>
          </div>
        </article>
      @empty
        <article class="news-card">
          <div class="news-body">
            <h4>Aucune actualité publiée pour le moment</h4>
            <p>Éleveurs, vendeurs, vétérinaires et livreurs peuvent partager des actualités depuis leur espace.</p>
          </div>
        </article>
      @endforelse
    </div>
  </div>
</section>

{{-- CTA FINAL --}}
<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="cta-final">
      <div>
        <h2>Rejoignez la communauté ElevConnect</h2>
        <p>Inscrivez-vous gratuitement, activez votre position et commencez à vendre, acheter ou intervenir dès aujourd'hui.</p>
      </div>
      <div class="cta-actions">
        <a href="{{ route('register') }}" class="btn btn-primary">Créer un compte gratuit</a>
        <a href="#" class="btn btn-outline-cream">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="#F3EBD8"><path d="M12 2a10 10 0 00-8.5 15.3L2 22l4.8-1.5A10 10 0 1012 2z"/></svg>
          Discuter sur WhatsApp
        </a>
      </div>
    </div>
  </div>
</section>

@endsection
