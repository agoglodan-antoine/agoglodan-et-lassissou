<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\CommandeFournisseurController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LivraisonController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\VersementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceVeterinaireController;
use App\Http\Controllers\VeterinaireController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes web
|--------------------------------------------------------------------------
| Deux grandes familles, qui correspondent aux deux layouts du site
| (resources/views/layouts) :
|
| - Layout "app" (public, avec en-tête Annonces/Vétérinaires/Actualités) :
|   pages librement consultables, plus les quelques pages authentifiées qui
|   restent volontairement sur ce layout (création de commande, paiement,
|   prise de rendez-vous — voir la note plus bas).
|
| - Layout "monEspace" (barre latérale + fil d'ariane) : absolument toutes
|   les pages de gestion, regroupées dans un unique groupe de routes
|   Route::prefix('mon-espace')->name('mon-espace.'), avec un sous-groupe
|   par module. C'est ce second groupe que ce fichier organise ci-dessous.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Catalogue public — consultable sans compte (l'exigence "Recherche par
// proximité" ne restreint pas la consultation aux seuls acheteurs connectés).
Route::get('/annonces', [CatalogueController::class, 'index'])->name('catalogue.index');
Route::get('/annonces/{annonce}', [CatalogueController::class, 'show'])->name('catalogue.show');

// Annuaire public des vétérinaires — même logique d'accès libre.
Route::get('/veterinaires', [VeterinaireController::class, 'index'])->name('veterinaires.index');
Route::get('/veterinaires/{veterinaire}', [VeterinaireController::class, 'show'])->name('veterinaires.show');

// Actualités — publiques en lecture (layout "app"), quel que soit le statut
// de connexion. La rédaction (créer/modifier/supprimer) est une action de
// gestion et vit donc dans le groupe "mon-espace" plus bas.
Route::get('/actualites', [ActualiteController::class, 'index'])->name('actualites.index');
Route::get('/actualites/{actualite}', [ActualiteController::class, 'show'])->name('actualites.show');

// Recherche générale — voir modal dans la nav (layouts/app.blade.php).
Route::get('/recherche', [SearchController::class, 'index'])->name('recherche.index');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [RegisterController::class, 'show'])->name('register');
    Route::post('/inscription', [RegisterController::class, 'register']);
    Route::get('/connexion', [LoginController::class, 'show'])->name('login');
    Route::post('/connexion', [LoginController::class, 'login']);

    Route::get('/mot-de-passe-oublie', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [ForgotPasswordController::class, 'send'])->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [ResetPasswordController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [LoginController::class, 'logout'])->name('logout');

    // Espace acheteur : passage de commande, paiement en séquestre, prise de
    // rendez-vous auprès d'un vétérinaire. NB : ces vues restent volontairement
    // sur le layout public "app" bien qu'elles nécessitent une authentification
    // — ce sont des actions ponctuelles déclenchées depuis une page publique
    // (une fiche annonce, un profil vétérinaire), pas des pages de gestion.
    // Tout le reste de l'espace personnel vit dans le groupe "mon-espace" ci-dessous.
    Route::get('/annonces/{annonce}/commander', [CommandeController::class, 'create'])->name('commandes.create');
    Route::get('/annonces/{annonce}/livreurs/rechercher', [CommandeController::class, 'rechercherLivreurs'])->name('commandes.livreurs.rechercher');
    Route::post('/annonces/{annonce}/commander', [CommandeController::class, 'store'])->name('commandes.store');

    Route::get('/mes-commandes/{commande}/paiement', [PaiementController::class, 'show'])->name('paiement.show');
    Route::post('/mes-commandes/{commande}/paiement', [PaiementController::class, 'process'])->name('paiement.process');

    Route::get('/veterinaires/{veterinaire}/rendez-vous', [RendezVousController::class, 'create'])->name('rendez-vous.create');
    Route::post('/veterinaires/{veterinaire}/rendez-vous', [RendezVousController::class, 'store'])->name('rendez-vous.store');

    // ======================================================================
    // ESPACE PERSONNEL — layout "monEspace" (barre latérale + fil d'ariane).
    // Un seul groupe, un sous-groupe par module. Le rôle est vérifié par le
    // middleware "role" (chap. 4 du mémoire) quand un module est réservé à
    // un ou plusieurs rôles précis ; sinon (profil, notifications, mes
    // commandes, mes rendez-vous...) l'accès est ouvert à tout utilisateur
    // authentifié et l'autorisation fine reste portée par les Policies.
    // ======================================================================
    Route::prefix('mon-espace')->name('mon-espace.')->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Gestion de profil — commune à tous les rôles.
        Route::prefix('profil')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'edit'])->name('edit');
            Route::put('/', [ProfileController::class, 'update'])->name('update');
            Route::put('/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('password');
        });

        // Centre de notifications, commun à tous les rôles.
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
        });

        // Mes achats (côté acheteur) : suivi, annulation, confirmation de
        // réception par QR, litige, avis.
        Route::prefix('commandes')->name('commandes.')->group(function () {
            Route::get('/', [CommandeController::class, 'index'])->name('index');
            Route::get('/{commande}', [CommandeController::class, 'show'])->name('show');
            Route::post('/{commande}/annuler', [CommandeController::class, 'annuler'])->name('annuler');
            Route::post('/{commande}/confirmer-reception', [CommandeController::class, 'confirmerReception'])->name('confirmer-reception');
            Route::post('/{commande}/signaler-probleme', [CommandeController::class, 'signalerProbleme'])->name('signaler-probleme');
            Route::post('/{commande}/noter', [CommandeController::class, 'noter'])->name('noter');
        });

        // Mes rendez-vous (côté éleveur) : suivi, annulation, avis. La
        // consultation (show) reste accessible au vétérinaire concerné via
        // RendezVousPolicy::view — seule la liste "index" est réservée à
        // l'éleveur, d'où le middleware posé route par route et non sur le
        // groupe entier.
        Route::prefix('rendez-vous')->name('rendez-vous.')->group(function () {
            Route::get('/', [RendezVousController::class, 'index'])->name('index')->middleware('role:eleveur');
            Route::get('/{rendezVous}', [RendezVousController::class, 'show'])->name('show');
            Route::post('/{rendezVous}/annuler', [RendezVousController::class, 'annuler'])->name('annuler');
            Route::post('/{rendezVous}/noter', [RendezVousController::class, 'noter'])->name('noter');
        });

        // Rédaction d'actualités — tout rôle sauf Acheteur (vérifié par
        // ActualitePolicy::create/update, pas par le middleware "role" ici
        // car plusieurs rôles différents y sont éligibles).
        Route::prefix('actualites')->name('actualites.')->group(function () {
            Route::get('/creer', [ActualiteController::class, 'create'])->name('create');
            Route::post('/', [ActualiteController::class, 'store'])->name('store');
            Route::get('/{actualite}/modifier', [ActualiteController::class, 'edit'])->name('edit');
            Route::put('/{actualite}', [ActualiteController::class, 'update'])->name('update');
            Route::delete('/{actualite}', [ActualiteController::class, 'destroy'])->name('destroy');
        });

        // Fournisseur (Éleveur / Vendeur de provende / Vendeur d'accessoires) :
        // gestion de ses propres annonces. Le rôle est vérifié par le middleware
        // "role" ; la propriété de l'annonce reste portée par AnnoncePolicy.
        Route::prefix('annonces')->name('annonces.')->middleware('role:eleveur,vendeur_provende,vendeur_accessoire')->group(function () {
            Route::get('/', [AnnonceController::class, 'index'])->name('index');
            Route::get('/creer', [AnnonceController::class, 'create'])->name('create');
            Route::post('/', [AnnonceController::class, 'store'])->name('store');
            Route::get('/{annonce}', [AnnonceController::class, 'show'])->name('show');
            Route::get('/{annonce}/modifier', [AnnonceController::class, 'edit'])->name('edit');
            Route::put('/{annonce}', [AnnonceController::class, 'update'])->name('update');
            Route::delete('/{annonce}', [AnnonceController::class, 'destroy'])->name('destroy');
        });

        // Fournisseur : traitement des commandes reçues sur ses annonces.
        Route::prefix('commandes-fournisseur')->name('commandes-fournisseur.')->middleware('role:eleveur,vendeur_provende,vendeur_accessoire')->group(function () {
            Route::get('/', [CommandeFournisseurController::class, 'index'])->name('index');
            Route::get('/{commande}', [CommandeFournisseurController::class, 'show'])->name('show');
            Route::post('/{commande}/prendre-en-charge', [CommandeFournisseurController::class, 'prendreEnCharge'])->name('prendre-en-charge');
            Route::post('/{commande}/valider', [CommandeFournisseurController::class, 'valider'])->name('valider');
            Route::post('/{commande}/refuser', [CommandeFournisseurController::class, 'refuser'])->name('refuser');
        });

        // Livreur : livraisons qui lui sont proposées (assignées par l'acheteur) et suivi des livraisons acceptées.
        Route::prefix('livraisons')->name('livraison.')->middleware('role:livreur')->group(function () {
            Route::get('/proposees', [LivraisonController::class, 'proposees'])->name('proposees');
            Route::get('/mes-livraisons', [LivraisonController::class, 'mesLivraisons'])->name('mes');
            Route::get('/{livraison}', [LivraisonController::class, 'show'])->name('show');
            Route::post('/{livraison}/accepter', [LivraisonController::class, 'accepter'])->name('accepter');
            Route::post('/{livraison}/rejeter', [LivraisonController::class, 'rejeter'])->name('rejeter');
            Route::post('/{livraison}/demarrer', [LivraisonController::class, 'demarrer'])->name('demarrer');
            Route::post('/{livraison}/livrer', [LivraisonController::class, 'livrer'])->name('livrer');
        });

        // Livreur : planning de disponibilité (créneaux d'indisponibilité).
        Route::prefix('planning')->name('planning.')->middleware('role:livreur')->group(function () {
            Route::get('/', [PlanningController::class, 'index'])->name('index');
            Route::post('/', [PlanningController::class, 'store'])->name('store');
            Route::delete('/{planning}', [PlanningController::class, 'destroy'])->name('destroy');
        });

        // Vétérinaire : services & tarifs.
        Route::prefix('services')->name('services.')->middleware('role:veterinaire')->group(function () {
            Route::get('/', [ServiceVeterinaireController::class, 'index'])->name('index');
            Route::get('/creer', [ServiceVeterinaireController::class, 'create'])->name('create');
            Route::post('/', [ServiceVeterinaireController::class, 'store'])->name('store');
            Route::get('/{service}', [ServiceVeterinaireController::class, 'show'])->name('show');
            Route::get('/{service}/modifier', [ServiceVeterinaireController::class, 'edit'])->name('edit');
            Route::put('/{service}', [ServiceVeterinaireController::class, 'update'])->name('update');
            Route::delete('/{service}', [ServiceVeterinaireController::class, 'destroy'])->name('destroy');
        });

        // Vétérinaire : rendez-vous reçus de la part des éleveurs.
        Route::prefix('rendez-vous-recus')->name('rendez-vous-recus.')->middleware('role:veterinaire')->group(function () {
            Route::get('/', [RendezVousController::class, 'recus'])->name('index');
            Route::post('/{rendezVous}/confirmer', [RendezVousController::class, 'confirmer'])->name('confirmer');
            Route::post('/{rendezVous}/refuser', [RendezVousController::class, 'refuser'])->name('refuser');
            Route::post('/{rendezVous}/realise', [RendezVousController::class, 'marquerRealise'])->name('realise');
        });

        // Vétérinaire : abonnement (formule Basique / Premium).
        Route::prefix('abonnement')->name('abonnement.')->middleware('role:veterinaire')->group(function () {
            Route::get('/', [AbonnementController::class, 'show'])->name('show');
            Route::post('/', [AbonnementController::class, 'souscrire'])->name('souscrire');
        });

        // Versements perçus (fournisseur ou livreur) — voir VersementController.
        Route::prefix('versements')->name('versements.')->middleware('role:eleveur,vendeur_provende,vendeur_accessoire,livreur')->group(function () {
            Route::get('/', [VersementController::class, 'index'])->name('index');
        });

        // Administration : vue d'ensemble, modération des annonces, gestion
        // des utilisateurs. Le traitement des litiges (statut en_litige) n'a
        // volontairement pas d'interface dédiée ici — conforme au mémoire,
        // qui le présente comme un axe d'évolution future (voir CONFORMITE_MEMOIRE.md).
        Route::prefix('administration')->name('admin.')->middleware('role:administrateur')->group(function () {
            Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
            Route::get('/moderation', [ModerationController::class, 'index'])->name('moderation.index');
            Route::post('/moderation/{annonce}/approuver', [ModerationController::class, 'approuver'])->name('moderation.approuver');
            Route::post('/moderation/{annonce}/rejeter', [ModerationController::class, 'rejeter'])->name('moderation.rejeter');

            Route::get('/utilisateurs', [UserController::class, 'index'])->name('utilisateurs.index');
            Route::get('/utilisateurs/{utilisateur}', [UserController::class, 'show'])->name('utilisateurs.show');
            Route::post('/utilisateurs/{utilisateur}/suspendre', [UserController::class, 'suspendre'])->name('utilisateurs.suspendre');
            Route::post('/utilisateurs/{utilisateur}/reactiver', [UserController::class, 'reactiver'])->name('utilisateurs.reactiver');
        });
    });
});
