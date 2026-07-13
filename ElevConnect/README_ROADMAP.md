# ElevConnect — Analyse & feuille de route

Ce document résume l'analyse du mémoire (*Memoire_Antoine_Zakari_V6_corrige.docx*),
du schéma SQL (*ElevConnect_schema_bdd_v4.sql*) et de la maquette d'accueil
(*Maquette_accueil.html*), ainsi que l'état d'avancement du développement.

## 1. Ce que dit le cahier des charges (résumé)

- **7 acteurs** : Éleveur, Acheteur, Vendeur de provende, Vendeur d'accessoires,
  Vétérinaire, Livreur, Administrateur — chacun avec un compte `UTILISATEURS`
  et une table de profil dédiée (1-1).
- **Catalogue unifié** : une seule table `ANNONCES` (type `animal` / `provende` /
  `accessoire`), soumise à **modération par l'Administrateur** avant d'être
  visible (`en_attente` → `visible` / `rejetee`).
- **Commande → Paiement → Livraison** : cycle d'état complet, paiement en ligne
  **détenu en séquestre**, libéré au fournisseur (et au livreur) après
  **vérification par code QR** à la réception.
- **Commission ElevConnect : 5 %**, prélevée sur le montant net de chaque
  commande *et* de chaque livraison, au moment du versement.
- **Abonnement réservé aux Vétérinaires uniquement** : formule Basique
  (gratuite, services limités) et Premium (2 000 FCFA/mois, services
  illimités, mise en avant, statistiques).
- **Aucune messagerie interne** sur la plateforme — la mise en relation passe
  par les rendez-vous, commandes et coordonnées de contact.
- **Rendez-vous vétérinaires** : pris en ligne, mais **payés hors plateforme**,
  directement entre l'éleveur et le vétérinaire.
- **Recherche par proximité** : chaque compte capture sa position GPS à
  l'inscription (localisation spatiale en base).
- Stack imposée : **Laravel 13 / PHP 8.3 / MySQL**, environnement local
  WampServer.

## 2. Écarts corrigés entre la maquette fournie et le cahier des charges

La maquette d'accueil (*Maquette_accueil.html*) est un très bon point de
départ visuel, mais son **texte marketing** contredisait à plusieurs endroits
les règles de gestion réelles du mémoire. Conformément à votre demande de
rester « strictement fidèle aux exigences du document », j'ai conservé
l'intégralité du **design** (palette, typographies, mise en page, animations)
mais corrigé le **contenu** suivant :

| Dans la maquette | Dans le cahier des charges | Correction appliquée |
|---|---|---|
| « 0 % de commission éleveur », « commandes sans commission » | Commission de **5 %** sur ventes et livraisons réalisées | Bandeau de confiance et étapes réécrits avec la commission réelle |
| « Paiement à la livraison » | Paiement en ligne **en séquestre**, libéré après scan du **QR code** | Bandeau et section traçabilité mis à jour |
| Abonnements **Éleveur** Basique/Premium à 5 000 FCFA/mois | Abonnement réservé aux **Vétérinaires**, Premium à **2 000 FCFA/mois** | Section « Abonnements » entièrement recentrée sur les vétérinaires |
| « Messagerie incluse / intégrée » (étape 3, cartes tarifs) | **Aucune messagerie interne** prévue | Mentions supprimées |
| 4 onglets acteurs (Éleveur, Acheteur, Vétérinaire, Livreur) | **7 acteurs**, dont Vendeur de provende / d'accessoires | Ajout des deux rôles manquants (Administrateur volontairement absent des onglets publics, voir §4) |
| Badges « Éleveur Premium/Basique » sur les annonces | Les éleveurs n'ont pas d'abonnement | Badges remplacés par le type d'annonce réel |

Par ailleurs, le script SQL (`PAIEMENTS.commission_sur_commande`) mentionne en
commentaire une commission de 2 %, alors que le mémoire (chapitres 2, 3 et le
résumé) fixe **5 %** de façon répétée et cohérente. J'ai retenu **5 %** comme
valeur de référence (`config('elevconnect.commission_rate')`), le texte du
mémoire faisant foi. Si 2 % était en réalité la valeur voulue, il suffit de
changer cette seule ligne de configuration.

## 3. Ce qui a été développé (Phase 1 — fondations)

- **Migrations** (`database/migrations/`) : les 22 tables du schéma SQL,
  traduites fidèlement (types, contraintes, clés étrangères, index), plus une
  migration portant les 3 triggers métier de premier niveau du script SQL
  (rôle ↔ type d'annonce, auteur d'actualité ≠ acheteur, interdiction de
  s'auto-commander).
- **Modèles Eloquent** (`app/Models/`) : un modèle par table, relations
  complètes (`Utilisateur` ↔ 7 profils de rôle, `Annonce`, `Commande`,
  `Paiement`, `Livraison`, `RendezVous`, `Abonnement`, etc.), constantes de
  statut, et un helper `Paiement::calculerCommission()`.
- **Authentification** : inscription différenciée par rôle avec capture de
  géolocalisation obligatoire (`RegisterController`), connexion/déconnexion
  (`LoginController`).
- **Page d'accueil** (`resources/views/welcome.blade.php`) : hero repris de
  la maquette (texte corrigé), catalogue par espèce, parcours utilisateur,
  annonces réellement issues de la base (`HomeController`), les 6 profils
  publics, abonnement vétérinaire, traçabilité, témoignages, actualités, CTA.
- **Configuration métier** (`config/elevconnect.php`) : taux de commission,
  grille tarifaire de l'abonnement vétérinaire.

## 4. Décisions prises faute de précision explicite du cahier des charges

- **Administrateur non inscriptible publiquement** : le mémoire ne précise
  pas de parcours d'auto-inscription pour ce rôle ; dans une plateforme réelle,
  les comptes admin sont provisionnés par un administrateur existant. Le
  formulaire public ne propose donc que les 6 autres rôles. *(Si vous
  souhaitez un compte admin auto-inscriptible, dites-le moi.)*
- **Contact WhatsApp** conservé en pied de page comme canal de support externe
  (pas une fonctionnalité de messagerie interne) — cohérent avec l'absence de
  messagerie sur la plateforme elle-même.

## 5. Ce qui a été développé (Phase 2 — Annonces & modération)

- **Espace fournisseur** (`app/Http/Controllers/AnnonceController.php`,
  routes `/mon-espace/annonces/*`) : publication, modification et suppression
  d'annonces par l'Éleveur / le Vendeur de provende / le Vendeur d'accessoires,
  avec formulaire adapté au type (poids/âge pour un animal, unité de mesure
  pour une provende/accessoire), upload des deux photos, et gestion du barème
  de réduction par tranche de quantité (`REDUCTIONS_ANNONCE`).
- **Règle de modération** : toute création *ou modification* d'annonce repasse
  au statut `en_attente` — elle ne redevient visible qu'après validation par
  un administrateur, conformément au cycle `en_attente → visible / rejetee`
  du cahier des charges.
- **File de modération Administrateur**
  (`app/Http/Controllers/Admin/ModerationController.php`, route
  `/administration/moderation`) : approbation ou rejet (avec motif
  obligatoire) des annonces en attente, avec notification automatique au
  fournisseur (`NOTIFICATIONS`).
- **Catalogue public** (`app/Http/Controllers/CatalogueController.php`,
  route `/annonces`) : recherche par type, prix maximum et **rayon de
  proximité réel** (requête SQL `ST_Distance_Sphere` sur la localisation
  spatiale du fournisseur, quel que soit son rôle), + fiche détaillée par
  annonce.
- **Autorisations** : `App\Policies\AnnoncePolicy` (auto-découverte par
  Laravel — aucun enregistrement manuel requis) pour la création/modification/
  suppression ; contrôle de rôle Administrateur fait en ligne dans
  `ModerationController` (voir note ci-dessous sur le middleware `role:`).
- **Page d'accueil** mise à jour : les annonces à la une et le menu renvoient
  désormais vers le vrai catalogue (`route('catalogue.index')`), plus de
  liens factices.

## 6. Ce qui a été développé (Phase 3 — Commandes & Paiement en séquestre)

- **Passage de commande** (`app/Http/Controllers/CommandeController.php`,
  routes `/annonces/{annonce}/commander`) : l'Acheteur choisit une quantité,
  le prix est recalculé en direct (JS) selon le barème de réduction de
  l'annonce (`Annonce::calculerMontant()`), la commande est créée avec un
  **code d'authenticité unique** (`Commande::genererCodeAuthenticite()`),
  destiné au QR code scanné à la livraison.
- **Paiement en séquestre** (`app/Http/Controllers/PaiementController.php`) :
  formulaire Mobile Money / carte bancaire, création de l'enregistrement
  `PAIEMENTS` avec calcul automatique de la **commission de 5 %**
  (`Paiement::calculerCommission()`), passage de la commande au statut
  `payee`. *Aucune passerelle réelle n'est intégrée* (le mémoire n'impose pas
  de prestataire précis) : le paiement est simulé et marqué `reussi`
  immédiatement — voir le commentaire en tête de `PaiementController` pour
  brancher une vraie passerelle en production.
- **Code QR** : généré avec le paquet `simplesoftwareio/simple-qrcode`
  (ajouté à `composer.json`, aucune dépendance réseau à l'exécution — rendu
  SVG pur PHP), affiché sur la page « Mes commandes » dès que la commande est
  payée.
- **Traitement côté fournisseur**
  (`app/Http/Controllers/CommandeFournisseurController.php`, routes
  `/mon-espace/commandes/*`) : prise en charge (`en_cours_de_traitement`),
  validation (`validee`) ou refus avec motif (`annulee` + notification à
  l'acheteur), conformément au cycle d'état `payee → en_cours_de_traitement →
  validee` du cahier des charges.
- **Annulation acheteur** : possible tant que la commande n'est pas encore
  `validee` (`CommandePolicy::annuler`).
- **Non couvert à ce stade** (volontairement laissé pour la Phase 4, qui
  porte spécifiquement sur la livraison) : affectation à un livreur, passage
  `en_cours_de_livraison → livree → confirmee`, scan effectif du QR code, et
  déclenchement des `VERSEMENTS` (au fournisseur et au livreur). Le
  remboursement (`statut_paiement = rembourse`) en cas de refus par le
  fournisseur est également noté en commentaire dans
  `CommandeFournisseurController::refuser()` mais pas encore implémenté.

## 7. Ce qui a été développé (Phase 4 — Livraison, QR & versements)

- **Ouverture automatique de la livraison** : quand le fournisseur valide une
  commande (`CommandeFournisseurController::valider()`), une entrée
  `LIVRAISON` est créée automatiquement (statut `en_attente`), avec les
  adresses fournisseur/client — plus besoin d'étape manuelle intermédiaire.
- **Espace Livreur** (`app/Http/Controllers/LivraisonController.php`, routes
  `/mon-espace/livraisons-disponibles` et `/mon-espace/mes-livraisons`) :
  - liste des livraisons non affectées ;
  - **acceptation** (`accepter`) : le livreur propose ses frais de livraison,
    le barème `REDUCTIONS_LIVRAISON` du livreur est appliqué selon la
    quantité de la commande (`Livraison::calculerFrais()`), et le
    verrouillage `lockForUpdate()` empêche deux livreurs d'accepter la même
    course ;
  - **démarrage** (`en_cours`) puis **remise** (`livree`) — deux étapes
    distinctes du cycle de vie du cahier des charges.
- **Mise à jour du séquestre à l'acceptation** : dès qu'un livreur accepte,
  `PaiementController`/`Paiement::calculerCommission()` est réappliqué au
  montant net de la livraison pour calculer la **commission de 5 % sur la
  livraison** (distincte de celle sur la commande) et le montant à verser au
  livreur.
- **Confirmation de réception par l'acheteur**
  (`CommandeController::confirmerReception()`) : l'acheteur saisit (ou
  scanne) son code de vérification unique ; une correspondance exacte avec
  `code_authenticite` fait passer la commande à `confirmee` et **déclenche
  les VERSEMENTS** (fournisseur, puis livreur si applicable), déduction faite
  des commissions.
- **Litige** (`signalerProbleme`) : l'acheteur peut signaler un problème
  après livraison plutôt que de confirmer — commande passée à `en_litige`,
  traitement complet renvoyé à l'Administration (Phase 6).
- **Avis** (`noter`) : note et commentaire sur le fournisseur et,
  séparément, sur le livreur ; la note moyenne du fournisseur
  (`note_moyenne`/`nombre_avis` sur sa table de profil) est recalculée
  automatiquement à chaque nouvel avis.
- **Remboursement** : si le fournisseur refuse la commande avant livraison
  (Phase 3), le paiement associé est désormais marqué `rembourse`.

> **Choix de conception à noter** : c'est l'**acheteur** qui confirme la
> réception (et non le livreur qui « scannerait » le client), afin que la
> libération des fonds en séquestre reste sous le contrôle de celui qui a
> payé — cohérent avec l'esprit de protection de l'acheteur du cahier des
> charges. Le champ `date_verification_qr` porte d'ailleurs, dans le schéma
> SQL d'origine, le commentaire *« scan du QR code par le client »*.

## 8. Ce qui a été développé (Phase 5 — Vétérinaires)

- **Annuaire public** (`app/Http/Controllers/VeterinaireController.php`,
  route `/veterinaires`) : recherche par spécialité et proximité (même
  logique `ST_Distance_Sphere` que le catalogue d'annonces), avec **mise en
  avant des profils Premium** en tête de liste — bénéfice concret de
  l'abonnement, conformément au cahier des charges.
- **Services & tarifs**
  (`app/Http/Controllers/ServiceVeterinaireController.php`, routes
  `/mon-espace/services/*`) : CRUD complet côté vétérinaire (titre,
  description, prix, durée, photo).
- **Rendez-vous** (`app/Http/Controllers/RendezVousController.php`) :
  l'Éleveur réserve depuis la fiche d'un vétérinaire (service optionnel,
  sujet, date) ; le vétérinaire confirme, refuse ou marque réalisé. **Aucun
  paiement n'est intégré ici** : le cahier des charges précise que la
  consultation se règle hors plateforme, directement entre les deux parties.
- **Abonnement** (`app/Http/Controllers/AbonnementController.php`, route
  `/mon-espace/abonnement`) : bascule Basique ↔ Premium, paiement simulé
  (même logique que `PaiementController`) pour le Premium à 2 000 FCFA/mois,
  historique des abonnements, un seul abonnement actif à la fois.
- **Limite de services en formule Basique** : `Veterinaire::estPremium()` et
  `limiteServicesAtteinte()` bloquent la publication au-delà d'un seuil. *Le
  mémoire mentionne des « services limités » sans fixer de chiffre* : la
  valeur retenue (3 services actifs) est un choix par défaut, modifiable
  dans `config('elevconnect.abonnement_veterinaire.basique.limite_services')`
  sans toucher au code.
- **Non couvert à ce stade** : renouvellement automatique de l'abonnement
  Premium à expiration (pas de tâche planifiée — le sandbox de développement
  n'a pas d'accès réseau/cron ; en production, une commande Artisan
  planifiée via `routes/console.php` marquerait les abonnements expirés).

## 9. Ce qui a été développé (Phase 6 — Administration, actualités, notifications)

- **Vue d'ensemble Administrateur** (`app/Http/Controllers/Admin/DashboardController.php`,
  route `/administration`) : annonces en attente, litiges ouverts, comptes
  actifs/suspendus, commandes confirmées.
- **Gestion des utilisateurs** (`app/Http/Controllers/Admin/UserController.php`,
  route `/administration/utilisateurs`) : recherche/filtre par rôle,
  **suspension / réactivation** de compte (`UTILISATEURS.statut`) — un
  administrateur ne peut pas suspendre un autre administrateur depuis cet
  écran.
- **Traitement des litiges** (`app/Http/Controllers/Admin/LitigeController.php`,
  route `/administration/litiges`) : les commandes `en_litige` (ouvertes par
  l'acheteur via `CommandeController::signalerProbleme()`, Phase 4) sont
  tranchées par l'administrateur, dans un sens ou dans l'autre :
  - **en faveur de l'acheteur** → commande `refusee`, paiement `rembourse` ;
  - **en faveur du fournisseur** → commande `confirmee`, **versements
    déclenchés** (fournisseur + livreur), comme une confirmation normale.
- **Actualités** (`app/Http/Controllers/ActualiteController.php`, route
  publique `/actualites`) : publication ouverte à **tout rôle sauf
  Acheteur** (`Utilisateur::peutPublierActualite()`, miroir du trigger SQL
  `trg_actualites_auteur_role`), avec pièces jointes (`ACTUALITES_MEDIA`).
  Contrairement aux annonces, **aucune modération n'est requise** par le
  cahier des charges pour ce module — publication directe. La page d'accueil
  affiche désormais les 3 actualités les plus récentes réellement publiées
  (au lieu des exemples statiques de la maquette d'origine).
- **Centre de notifications** (`app/Http/Controllers/NotificationController.php`,
  route `/notifications`) : toutes les notifications générées depuis la
  Phase 2 (modération, commandes, livraisons, rendez-vous, litiges) sont
  désormais consultables par l'utilisateur, avec marquage automatique comme
  lues à l'ouverture.

Avec cette phase, **les six exigences fonctionnelles majeures du cahier des
charges sont couvertes** : gestion des sept acteurs, catalogue unifié modéré,
commandes/paiement en séquestre, livraison avec vérification QR et
versements, services et rendez-vous vétérinaires avec abonnement, et
administration (modération, utilisateurs, litiges, actualités,
notifications).

## 10. Corrections apportées suite à vos retours

1. **Commande ouverte à tous les rôles** : `CommandePolicy::create()` ne
   restreint plus la commande au rôle Acheteur — tout utilisateur connecté
   peut commander (un Éleveur peut acheter de la provende, un Vétérinaire
   des accessoires, etc.), à la seule exception de sa propre annonce
   (vérifié dans `CommandeController`). Le bouton « Commander » de la fiche
   annonce n'est donc plus masqué pour les autres rôles.
2. **Vrai tableau de bord par rôle** : `DashboardController` (nouveau)
   affiche désormais des indicateurs et des raccourcis adaptés à chaque
   rôle sur `/tableau-de-bord`, au lieu d'une redirection directe vers un
   seul module. L'Administrateur garde sa propre vue d'ensemble dédiée.
3. **Cartes de largeur/hauteur homogènes** : les grilles de cartes
   (catalogue, vétérinaires, actualités, recherche) utilisent désormais
   `display:flex` en colonne avec image à ratio fixe (4:3) et texte tronqué
   proprement (`-webkit-line-clamp`), pour que toutes les cartes d'une
   même rangée aient la même taille quel que soit leur contenu. Les
   annonces du carrousel d'accueil suivent la même logique.
4. **Responsive renforcé** : ajout de `box-sizing:border-box` global et
   `overflow-x:hidden` sur `html`/`body` (aucun élément ne peut plus faire
   déborder la page horizontalement) ; tous les tableaux (`dash-table`)
   sont enveloppés dans `.table-responsive` (défilement horizontal dédié
   plutôt qu'un débordement) ; formulaires et barres de filtre repassent
   en une colonne sous 760px.
5. **Recherche générale** : le champ de recherche a été retiré du hero
   (simplifié) et déplacé dans un **bouton de la barre de navigation**
   (icône loupe, desktop et mobile), qui ouvre une **modale** avec un champ
   de saisie unique. La validation renvoie vers `/recherche`
   (`SearchController` + `resources/views/recherche/index.blade.php`), qui
   affiche les résultats **catégorisés** : Annonces, Actualités, Services
   vétérinaires, Services de transport (profils Livreur).
6. **Erreur `Blueprint::point does not exist` corrigée** : les colonnes de
   géolocalisation utilisaient le type spatial `POINT` (`$table->point(...)`,
   `ST_Distance_Sphere`), indisponible sur l'installation Laravel testée.
   Remplacé partout par deux colonnes classiques `latitude`/`longitude`
   (`DECIMAL(10,7)`) et un calcul de distance en SQL standard (**formule de
   Haversine**), sans aucune dépendance à une extension spatiale — voir
   `CatalogueController` et `VeterinaireController`.
7. **`latitude`/`longitude` déplacées sur UTILISATEURS** : ces deux colonnes
   étaient dupliquées dans les 7 tables de profil (ELEVEURS, ACHETEURS,
   VENDEUR_PROVENDE, VENDEUR_ACCESSOIRE, VETERINAIRES, LIVREURS,
   ADMINISTRATEURS) alors qu'elles portent la même information pour tout le
   monde (position GPS captée à l'inscription). Elles vivent désormais une
   seule fois sur `UTILISATEURS` ; les 7 tables de profil ne gardent que
   leurs attributs propres. `RegisterController`, `CatalogueController` et
   `VeterinaireController` ont été mis à jour en conséquence (`u.latitude` /
   `u.longitude` au lieu de `el.latitude`, `v.latitude`, etc.).

**Si vous aviez déjà lancé `php artisan migrate`, relancez avec
`php artisan migrate:fresh`** pour repartir d'un schéma propre à chaque
correction de migration listée ci-dessus.

## 11. Installation (WampServer / PHP 8.3 / MySQL)

Le bac à sable de développement ne dispose pas de PHP/Composer ni d'accès
réseau : les fichiers livrés sont donc le **code source complet** de
l'application, à intégrer dans un projet Laravel fraîchement installé :

```bash
composer create-project laravel/laravel elevconnect "13.*"
cd elevconnect
# copier par-dessus : app/, config/elevconnect.php, database/migrations/,
# resources/views/, routes/web.php, public/css/, public/js/, .env.example, composer.json
composer install
cp .env.example .env
php artisan key:generate
# créer la base "elevconnect" dans phpMyAdmin, puis :
php artisan migrate
php artisan storage:link   # requis pour afficher les photos d'annonces
php artisan serve
```

**Middleware de rôle (optionnel, recommandé pour la suite)** : un middleware
générique `App\Http\Middleware\EnsureRole` est fourni mais pas encore
enregistré (le fichier `bootstrap/app.php`, généré par `composer
create-project`, n'est pas fourni dans ce dépôt). Pour l'activer :

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
})
```

Une fois fait, les routes `/administration/*` peuvent utiliser
`Route::middleware('role:administrateur')` au lieu du contrôle actuel fait en
ligne dans `ModerationController::ensureAdministrateur()`.

## 12. État d'avancement & pistes d'amélioration restantes

- [x] **Phase 1 — Fondations** : schéma, modèles, authentification, accueil.
- [x] **Phase 2 — Annonces** : CRUD fournisseur, modération, catalogue public,
      recherche par proximité.
- [x] **Phase 3 — Commandes & Paiement** : passage de commande, paiement en
      séquestre (simulé), commission de 5 %, génération du code QR,
      traitement de la commande côté fournisseur jusqu'à `validee`.
- [x] **Phase 4 — Livraison** : espace livreur, acceptation avec barème de
      réduction, confirmation de réception par l'acheteur, versements
      (fournisseur + livreur), litige, avis.
- [x] **Phase 5 — Vétérinaires** : annuaire public, services & tarifs,
      rendez-vous, abonnements (Basique/Premium, paiement simulé).
- [x] **Phase 6 — Administration** : vue d'ensemble, gestion des
      utilisateurs, traitement des litiges, actualités, notifications.

Toutes les exigences fonctionnelles majeures identifiées dans le mémoire sont
désormais couvertes par du code réel (et non de simples maquettes). Ce qui
reste volontairement hors périmètre, documenté au fil des sections
ci-dessus, et qui relève surtout de l'intégration avec des services
externes réels plutôt que de la logique métier :

- Intégration d'une **vraie passerelle de paiement** (Mobile Money / carte
  bancaire) à la place des simulations de `PaiementController` et
  `AbonnementController`.
- **Tâche planifiée** (cron / `routes/console.php`) pour expirer
  automatiquement les abonnements Premium arrivés à échéance.
- Middleware `role:` à enregistrer dans `bootstrap/app.php` pour remplacer
  les contrôles de rôle faits en ligne dans les contrôleurs `Admin\*`.
- Tests automatisés (PHPUnit) — aucun test n'a été écrit à ce stade, faute
  d'environnement PHP exécutable dans ce bac à sable pour les valider.

N'hésitez pas à me signaler tout écart avec votre mémoire que je n'aurais
pas identifié : je peux ajuster le code en conséquence.
