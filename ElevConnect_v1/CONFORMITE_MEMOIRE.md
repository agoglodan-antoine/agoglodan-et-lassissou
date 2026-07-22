# Audit de conformité — ElevConnect vs. le mémoire

Cet audit compare l'application développée au texte intégral du mémoire
(*Memoire_Antoine_Zakari_V6_corrige.docx*), chapitre par chapitre. Il est
organisé en trois niveaux : ce qui est **conforme**, ce qui est **partiellement
conforme ou a été volontairement adapté**, et ce qui est **manquant**.

---

## 1. Bonne nouvelle : le cœur métier est conforme

- **Sept acteurs** avec spécialisation par table (§4.II.1) : conforme.
- **Catalogue unifié** (`ANNONCES`, discriminé par `type_annonce`, déduit du
  rôle) : conforme.
- **Cycle de vie de la commande** (`en_attente → payee → en_cours_de_traitement
  → validee → [en_cours_de_livraison] → livree → confirmee`, avec `annulee`
  avant validation et `refusee`/`en_litige` entre livraison et confirmation) :
  conforme.
- **Commission de 5 %** sur commande et livraison, prélevée au versement :
  conforme.
- **Séquestre + code QR** vérifié par l'acheteur à la réception, déclenchant
  les versements : conforme.
- **Abonnement vétérinaire** Basique/Premium à 2000 FCFA/mois, seul rôle
  éligible : conforme.
- **Modération des annonces** par l'administrateur (`en_attente` →
  `visible`/`rejetee`, motif de rejet) : conforme.
- **Actualités publiables par tout rôle sauf Acheteur** (`peutPublierActualite()`
  + trigger SQL `trg_actualites_auteur_role`) : conforme.
- **Paiement du rendez-vous vétérinaire hors plateforme** : conforme (aucune
  intégration de paiement dans `RendezVousController`).
- **Barèmes de réduction** (annonce et livraison) par tranche de quantité :
  conformes.
- **Suppression douce** sur `UTILISATEURS`, `ANNONCES`, `COMMANDES` : conforme.
- **Contraintes `ON DELETE`** (CASCADE sur les tables de spécialisation et
  `ACTUALITES_MEDIA`, RESTRICT sur `COMMANDES`/`RENDEZ_VOUS`/`VERSEMENTS`, SET
  NULL sur `LIVRAISON.id_livreur`) : conformes.
- **Hachage bcrypt**, validation serveur systématique, protection CSRF/XSS/SQLi
  héritée de Laravel : conformes.
- **Position GPS captée via `navigator.geolocation`** au consentement de
  l'utilisateur : conforme.

---

## 2. Points volontairement adaptés (avec la raison)

| Point du mémoire | Ce qui a été fait | Pourquoi |
|---|---|---|
| §4.II.1 : colonne `localisation` de type spatial **POINT**, avec **index spatial**, répétée sur **chacune des 7 tables de rôle** | Deux colonnes `latitude`/`longitude` (DECIMAL), stockées **une seule fois sur `UTILISATEURS`**, distance calculée par formule de Haversine en SQL standard | 1) Le type `POINT` via `$table->point()` n'était pas disponible sur votre installation Laravel (erreur bloquante rencontrée). 2) Vous avez ensuite demandé explicitement à supprimer la répétition sur les 7 tables. **Point d'attention** : ce choix diverge du texte explicite du chapitre 4 (§II.2, *"la colonne localisation ... de chaque table de rôle est déclarée NOT NULL et couverte par un index spatial"*), même s'il reste cohérent avec la description du MCD au chapitre 3 (*"L'entité centrale UTILISATEUR porte ... la localisation GPS"*). Le mémoire n'est donc pas rigoureusement homogène sur ce point entre le chapitre 3 et le chapitre 4 ; le choix actuel privilégie la cohérence chapitre 3 + votre demande explicite, au prix d'un écart avec la formulation du chapitre 4. |
| Front-end en **Tailwind CSS + Bootstrap 5** (§4.IV) | CSS entièrement custom, reprise de la maquette que vous avez fournie | La maquette livrée (*Maquette_accueil.html*) n'utilise elle-même ni Tailwind ni Bootstrap — j'ai suivi votre maquette plutôt que la stack mentionnée dans le mémoire, comme demandé initialement ("utilise la section hero du fichier maquette"). |
| Litige (`en_litige`) : *"traitement complet non développé dans le cadre de ce projet"* (chap. 3 et 4) | Un `Admin\LitigeController` complet (arbitrage en faveur de l'acheteur ou du fournisseur, versements/remboursement) a été développé | Ajout allant au-delà du périmètre explicitement déclaré hors-scope par le mémoire — bonus, pas un défaut, mais à signaler puisque le mémoire dit explicitement ne pas l'avoir prévu. |

---

## 3. Écarts réels à corriger

### 3.1 — Assignation de la livraison (le plus important) — ✅ CORRIGÉ

Le mémoire est cohérent sur ce point à plusieurs endroits :
- Tableau 2.4 : l'acheteur *"choisit un livreur pour la réception de sa commande"*.
- Cas d'utilisation "Passer une commande" (chap. 3) : *"le livreur **assigné** est notifié si un livreur intervient"*.
- Tableau des rôles (chap. 3) : le livreur *"consulte les livraisons **proposées** et les prend en charge ou les **rejette**"*.
- Plan de test (chap. 5, module G) : *"Rejet d'une livraison par un livreur indisponible → statut `rejetee`, livraison **proposée à un autre livreur**"*.
- Chap. 5, interface Livreur : *"commandes qui lui sont **assignées**, une fois confirmées par l'éleveur"*.

**Correction apportée** (voir README_ROADMAP.md, §10.9) : l'acheteur choisit
désormais un livreur précis à la commande (trié par proximité avec le
fournisseur) ; ce livreur est notifié et peut accepter ou rejeter la
livraison qui lui est spécifiquement assignée ; en cas de rejet, elle est
reproposée automatiquement au livreur disponible le plus proche, avec
historique des refus pour éviter les doublons.

### 3.2 — Livraison réellement optionnelle (self-pickup) — ✅ CORRIGÉ

Le mémoire précise à plusieurs reprises : *"en l'absence de livreur, la
commande passe directement de `validee` à `confirmee` dès la vérification
par l'acheteur"* (règles de gestion, cas d'utilisation, et test C du chap. 5).

**Correction apportée** (voir README_ROADMAP.md, §10.9) : l'acheteur choisit
désormais explicitement "retrait direct" à la commande ; dans ce cas,
aucune `LIVRAISON` n'est créée, et la commande passe directement de
`validee` à `confirmee` sans vérification par code QR, conformément au
texte du mémoire.

### 3.3 — Scan du QR code via la caméra (MediaDevices API)

Le mémoire précise (§4.IV) : *"un composant JavaScript dédié (accès à la
caméra via l'API MediaDevices) permet à l'acheteur de scanner le code QR"*.

**Ce qui a été développé** : un simple champ texte où l'acheteur saisit le
code manuellement — aucun accès caméra.

→ **Écart confirmé.**

### 3.4 — Système de notifications natif Laravel (DB + email)

Le mémoire précise (§4.V) : *"le système Notification natif de Laravel est
utilisé ... avec un canal base de données (table NOTIFICATIONS) et un canal
courriel"*.

**Ce qui a été développé** : un modèle Eloquent `NotificationElevConnect`
maison, alimenté par de simples `::create()` dans les contrôleurs — aucune
classe `Illuminate\Notifications\Notification`, aucun canal email.

→ **Écart confirmé** (les notifications fonctionnent, mais pas selon
l'architecture native décrite, et sans email du tout).

### 3.5 — Tâche planifiée d'expiration des abonnements

Le mémoire précise (§4.V) une commande Artisan planifiée quotidiennement
(`abonnements:verifier-expiration`).

**Ce qui a été développé** : rien — les abonnements Premium expirés au-delà
de leur `date_expiration` ne changent jamais automatiquement de statut
(`Veterinaire::estPremium()` vérifie la date à la volée, ce qui masque le
problème côté affichage, mais le `statut` en base reste `actif` indéfiniment).

→ **Écart confirmé** — déjà noté dans le README, mais à corriger : c'est un
cas de test explicite du chapitre 5 (module E).

### 3.6 — Réinitialisation de mot de passe — ✅ CORRIGÉ

Exigence fonctionnelle explicite (chap. 2, module "Gestion des comptes") et
cas de test explicite (chap. 5, module A) : *"Lien de réinitialisation reçu
par email"*.

**Correction apportée** (voir README_ROADMAP.md, §10.10) :
`ForgotPasswordController` + `ResetPasswordController`, basés sur le broker
de mots de passe natif de Laravel. **Nécessite un réglage manuel de
`config/auth.php`** (fichier non livré dans ce dépôt) — voir le README pour
la configuration exacte à appliquer.

### 3.7 — Gestion de profil (modifier ses informations) — ✅ CORRIGÉ

Exigence fonctionnelle explicite (chap. 2) : *"gestion de profil"*, pour
tous les rôles.

**Correction apportée** (voir README_ROADMAP.md, §10.12) :
`ProfileController` (`/mon-profil`), informations communes + attributs de
rôle + recapture de la position GPS + changement de mot de passe séparé.

### 3.8 — Planning de disponibilité du livreur

Le modèle `PLANNING_LIVREUR` existe en base (migration + modèle Eloquent),
mais **aucun contrôleur, aucune vue, aucune route** ne permet au livreur de
le consulter ou de le renseigner, et la logique d'acceptation d'une
livraison ne vérifie jamais ce planning avant d'accepter.

→ **Écart confirmé.**

### 3.9 — Politique de mot de passe — ✅ CORRIGÉ

Le mémoire précise (§4.VI.D) : *"longueur minimale de 8 caractères,
présence d'au moins une lettre et un chiffre"*.

**Correction apportée** : règle centralisée dans
`ResetPasswordController::REGLE_MOT_DE_PASSE` (regex lettre + chiffre),
appliquée à la fois à l'inscription et à la réinitialisation.

### 3.10 — Architecture back-end : détails structurels

- **Middleware de rôle** : la classe `EnsureRole` existe mais n'est jamais
  enregistrée ni utilisée (`role:administrateur` etc.) — les contrôles se
  font actuellement en ligne dans chaque contrôleur (`abort_unless(...)`).
  Le mémoire (§4.V) décrit explicitement ce middleware comme actif.
- **`VersementController`** explicitement cité (§4.V) : n'existe pas — la
  création des versements est faite en ligne dans `CommandeController` et
  `Admin\LitigeController`.
- **Composants Blade réutilisables** (`components/annonce-card.blade.php`,
  `components/statut-badge.blade.php`, `components/navbar.blade.php`,
  §4.IV) : n'existent pas — le HTML des cartes est dupliqué dans chaque vue.
- **Form Requests dédiées par module** (`StoreCommandeRequest`, etc.,
  §4.V) : seule `StoreAnnonceRequest`/`UpdateAnnonceRequest` existent : la
  validation des commandes, paiements, rendez-vous, etc. est faite en ligne
  dans les contrôleurs via `$request->validate()`.
- **Tableaux de bord "une seule page" par rôle** (conclusion, *"design ...
  restreint à une seule page pour les tableaux de bord métiers"*) : le
  tableau de bord unifié (`DashboardController`) respecte cet esprit, mais
  chaque module (annonces, commandes, livraisons...) reste sur ses propres
  pages dédiées plutôt que condensé sur l'unique page de bord — à clarifier
  si "une seule page" visait le tableau de bord lui-même (respecté) ou
  l'ensemble des opérations d'un rôle (non respecté).

Ces derniers points n'empêchent rien de fonctionner, mais s'écartent de
l'architecture précisément décrite au chapitre 4 — pertinent si un
correcteur compare le code aux diagrammes de classes/séquence.

---

## 4. Priorisation proposée

| Priorité | Écart | Effort estimé | Statut |
|---|---|---|---|
| 1 | Assignation de la livraison à un livreur choisi (3.1) + livraison optionnelle (3.2) | Élevé — retouche du flux de commande | ✅ Fait |
| 2 | Réinitialisation de mot de passe (3.6) | Faible — fonctionnalité standard Laravel | ✅ Fait |
| 3 | Gestion de profil (3.7) | Moyen | ✅ Fait |
| 4 | Tâche planifiée d'expiration des abonnements (3.5) | Faible | À faire |
| 5 | Planning de disponibilité du livreur (3.8) | Moyen | À faire |
| 6 | Politique de mot de passe (3.9) | Très faible | ✅ Fait |
| 7 | Scan QR par caméra (3.3) | Moyen (JS + permissions caméra) | À faire |
| 8 | Notifications natives Laravel + email (3.4) | Moyen | À faire |
| 9 | Middleware de rôle, VersementController, composants Blade, Form Requests dédiées (3.10) | Moyen — refactoring sans changement fonctionnel | À faire |

Je peux commencer par le point 1 (le plus structurant, puisqu'il touche au
cœur du cycle de commande/livraison), puis enchaîner sur la liste dans
l'ordre proposé — dites-moi comment vous souhaitez procéder.
