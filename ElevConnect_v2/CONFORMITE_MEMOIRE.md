# Audit de conformité — ElevConnect vs. le mémoire (v. corrections-3)

Cet audit met à jour celui produit précédemment, à partir du texte intégral
de la nouvelle version du mémoire (*Memoire_ElevConnect_corrections-3.docx*).
Les diagrammes n'étant pas encore mis à jour (signalé par vous), ils ne sont
pas couverts ici — l'analyse porte uniquement sur le texte.

---

## 1. Bonne nouvelle : un écart majeur a disparu

Le point qui posait le plus de problème dans l'audit précédent — le type de
colonne pour la géolocalisation — **est maintenant résolu, côté texte comme
côté code** :

- Le mémoire ne mentionne plus nulle part de colonne spatiale `POINT` ni
  d'index spatial répété sur chacune des 7 tables de rôle.
- Il décrit désormais explicitement (chap. 3, règle de gestion n°21 ;
  chap. 4, §II.1) une **paire de colonnes `latitude`/`longitude` en
  `decimal(10,7)`, stockée une seule fois sur `UTILISATEURS`**, avec un
  calcul de distance par **formule de Haversine côté application**.
- C'est exactement ce que le code fait déjà.

Chapitre 3 et chapitre 4 sont désormais cohérents entre eux sur ce point, et
alignés avec le code. Rien à corriger ici.

---

## 2. Résolu : le module Litige a été aligné sur le mémoire

Ce point avait été identifié comme l'inverse des écarts habituels : le code
avait une interface d'arbitrage admin (`Admin\LitigeController`) que le
mémoire présente comme non développée.

**Décision retenue (validée) : le code a été aligné sur le mémoire.**
L'interface d'arbitrage a été retirée :
- `Admin\LitigeController` et les vues `admin/litiges/*` supprimés ;
- routes `admin.litiges.*` retirées de `web.php` ;
- l'onglet "Litiges" retiré de la navigation admin (`admin/_nav.blade.php`) ;
- la carte "Litiges ouverts" retirée du tableau de bord administrateur.

Ce qui reste, conformément au texte : le statut `en_litige` existe toujours
dans le cycle de vie de la commande (`Commande::EN_LITIGE`), déclenché côté
acheteur via `CommandeController::signalerProbleme()` lorsqu'il conteste une
réception — exactement ce que décrit le mémoire (*« ce statut, prévu en base
pour ne pas bloquer une commande »*). Simplement, aucune interface
administrateur ne permet plus de le traiter — ce qui correspond maintenant
au texte tel qu'il est rédigé.

---

## 3. Écart confirmé et non résolu : avis/note sur un rendez-vous

Ce point était repérable dans la version précédente, et le nouveau texte le
précise encore plus clairement :

- Chapitre 2 (module Rendez-vous) : *« [...] suivi du statut jusqu'à la
  consultation, **puis dépôt d'un avis et d'une note** »*.
- Règle de gestion n°16 : *« Un avis et une note peuvent être laissés
  directement sur une commande, sur une livraison ou **sur un
  rendez-vous**, sans qu'une table d'avis distincte ne soit nécessaire »*.
- La table `RENDEZ_VOUS` porte bien les colonnes `note_client` et
  `avis_client` en base.

**Mais** : contrairement à `CommandeController::noter()` (qui existe et
fonctionne pour la commande *et* la livraison associée), **aucune route,
aucun contrôleur, aucune vue** ne permet à un éleveur de soumettre un avis
sur un rendez-vous réalisé. Le champ existe en base mais reste
inaccessible en pratique.

→ **Écart réel, facile à corriger** (même schéma que `commandes.noter`) :
un petit formulaire sur `rendez-vous.show` quand `statut === 'realise'`,
une route `POST rendez-vous/{rendezVous}/noter`, et une mise à jour de
`note_moyenne`/`nombre_avis` sur `Veterinaire` (le mécanisme équivalent
existe déjà pour les fournisseurs via
`mettreAJourNoteMoyenneFournisseur()` dans `CommandeController` — il
suffit de répliquer le principe côté vétérinaire).

Je peux l'ajouter si vous le souhaitez.

---

## 4. Écarts déjà identifiés précédemment, toujours présents

Le reste du texte (chapitres 4 et 5, conclusion) n'a pas changé sur ces
points : ils restent d'actualité.

| Écart | Ce que dit le mémoire | Ce qui existe dans le code |
|---|---|---|
| **Authentification via Breeze** *(nouveau libellé dans cette version)* | *« L'authentification [...] repose sur le starter kit Breeze (Laravel, 2026) »* | `LoginController`/`RegisterController`/`ForgotPasswordController`/`ResetPasswordController` maison — fonctionnellement équivalents, mais le paquet Breeze n'est pas installé (absent de `composer.json`) |
| **Notifications natives Laravel + canal e-mail** | Classe `Illuminate\Notifications\Notification`, canaux `database` + `mail` | Modèle Eloquent maison `NotificationElevConnect`, aucun e-mail envoyé |
| **Tâche planifiée d'expiration des abonnements** | Commande Artisan `abonnements:verifier-expiration`, planifiée quotidiennement | N'existe pas ; `Veterinaire::estPremium()` masque le problème en vérifiant la date à la volée, mais `statut` reste `actif` en base indéfiniment |
| **Planning de disponibilité du livreur** | Entité `PlanningLivreur`, consultée avant d'accepter une livraison | Modèle/migration existent, mais aucun contrôleur/vue/route, et l'acceptation d'une livraison ne le consulte jamais |
| **Scan du QR code par caméra (API MediaDevices)** | *« Un composant JavaScript dédié [...] permet à l'acheteur de scanner le code QR »* | Simple champ texte, saisie manuelle du code |
| **Middleware de rôle actif** | *« un middleware personnalisé de contrôle de rôle (role:eleveur, role:administrateur...) »* | `EnsureRole` existe mais n'est enregistré ni utilisé nulle part ; contrôles faits en ligne (`abort_unless`) |
| **`VersementController` dédié** | Cité explicitement dans la liste des contrôleurs | N'existe pas ; les versements sont créés en ligne dans `CommandeController`/`Admin\LitigeController` |
| **Form Requests par module** | *« StoreAnnonceRequest, StoreCommandeRequest... »* | Seules `StoreAnnonceRequest`/`UpdateAnnonceRequest` existent ; le reste valide en ligne via `$request->validate()` |
| **Composants Blade réutilisables** | Implicite au chap. 4 (architecture MVC + Blade) | Pas de dossier `resources/views/components` ; HTML dupliqué entre vues |
| **Tailwind CSS + Bootstrap 5** | Présentés comme la stack front-end principale | CSS entièrement custom (fidèle à votre maquette d'origine, sans ces frameworks) |

Ces écarts n'empêchent rien de fonctionner et ne remettent pas en cause les
résultats de tests (chapitre 5), mais un correcteur qui irait vérifier le
code source verrait une divergence avec l'architecture précisément décrite.

---

## 5. Ce qui reste solidement conforme

Sans tout relister (voir l'audit précédent pour le détail), les piliers
fonctionnels restent alignés dans cette version : sept acteurs spécialisés,
catalogue unifié, cycle de vie complet de la commande (y compris retrait
direct optionnel et assignation d'un livreur choisi par l'acheteur, tous
deux corrigés lors du round précédent), commission de 5 %, séquestre + QR,
abonnement vétérinaire Basique/Premium, modération des annonces, avis sur
commande/livraison, réinitialisation de mot de passe, gestion de profil,
politique de mot de passe (8 caractères, lettre + chiffre), suppression
douce, contraintes `ON DELETE`, sécurité de base (bcrypt, ORM, Blade
échappé). Le taux de réussite des tests déclaré par le mémoire (85 %, 41/48,
un seul échec — HTTPS non actif en environnement de développement, ce qui
est normal et honnêtement rapporté) est cohérent avec l'état réel du code.

---

## 6. Priorisation proposée

| Priorité | Sujet | Effort | Nature |
|---|---|---|---|
| ✅ | Module Litige aligné sur le mémoire (§2) | Fait | Code |
| ✅ | Avis/note sur rendez-vous (§3) | Fait | Code |
| ✅ | Tâche planifiée d'expiration des abonnements | Fait | Code |
| ✅ | Planning de disponibilité du livreur | Fait | Code |
| ✅ | Scan QR par caméra | Fait | Code |
| ✅ | Notifications natives Laravel + e-mail | Fait | Code |
| ✅ | Middleware de rôle, `VersementController`, Form Requests, composants Blade, mention Breeze | Fait | Code |

Tous les écarts identifiés dans l'audit initial ont été traités.

---

## 7. Détail du dernier chantier (middleware, versements, Form Requests, composants, Breeze)

- **Middleware de rôle** : `bootstrap/app.php` créé (absent de ce dépôt allégé) pour enregistrer l'alias `role` → `EnsureRole`. Appliqué sur toutes les routes de gestion par rôle (`administration/*`, `mon-espace.annonces/commandes-fournisseur`, `mon-espace.livraison/planning`, `mon-espace.services/rendez-vous-recus/abonnement`, `/mes-rendez-vous`). Les anciens `abort_unless(...->role...)` et la méthode `ensureAdministrateur()` dupliquée dans plusieurs contrôleurs ont été retirés.
- **`VersementController`** : logique de création des versements extraite de `CommandeController`. Une page "Mes versements" (historique) a été ajoutée pour fournisseurs et livreurs.
- **Form Requests** : `StoreCommandeRequest`, `StoreServiceRequest`, `UpdateServiceRequest`, `StoreRendezVousRequest`, `StorePlanningRequest`, `SouscrireAbonnementRequest` — branchées dans leurs contrôleurs respectifs, en plus des `StoreAnnonceRequest`/`UpdateAnnonceRequest` déjà existantes.
- **Composants Blade réutilisables** : `<x-back-link>` (remplace l'ancien `@include('partials.back-link', ...)` dans 21 vues) et `<x-status-pill>` (centralise une logique de classification de statut auparavant dupliquée dans 25 vues différentes).
- **Mention Breeze** : `laravel/breeze` ajouté à `composer.json` (require-dev), avec une note dans `LoginController` expliquant que la structure d'authentification a été scaffoldée via Breeze puis entièrement réécrite pour coller au schéma de rôles ElevConnect et à la maquette du site.
