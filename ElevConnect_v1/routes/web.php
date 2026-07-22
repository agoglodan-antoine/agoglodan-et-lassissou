<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LitigeController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\ActualiteController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\CommandeFournisseurController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LivraisonController;
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
| Deux familles de pages, chacune avec sa mise en page (voir
| README_ROADMAP.md) :
|
| 1. Layout `layouts.app` (public, avec ou sans compte) : accueil,
|    catalogue, annuaire vétérinaires, actualités (lecture), recherche
|    générale, authentification — PLUS, bien qu'authentification requise :
|    passage de commande, paiement, prise de rendez-vous vétérinaire (ces
|    trois parcours restent volontairement sur le layout public).
|
| 2. Layout `layouts.mon-espace` (barre latérale) : tout le reste de
|    l'espace connecté — tableau de bord, gestion des annonces, commandes
|    reçues/passées, livraisons, services, rendez-vous, abonnement, profil,
|    notifications, rédaction d'actualités, et l'administration.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Catalogue public — consultable sans compte (l'exigence "Recherche par
// proximité" ne restreint pas la consultation aux seuls acheteurs connectés).
Route::get('/annonces', [CatalogueController::class, 'index'])->name('catalogue.index');
Route::get('/annonces/{annonce}', [CatalogueController::class, 'show'])->name('catalogue.show');

// Annuaire public des vétérinaires — même logique d'accès libre.
Route::get('/veterinaires', [VeterinaireController::class, 'index'])->name('veterinaires.index');
Route::get('/veterinaires/{veterinaire}', [VeterinaireController::class, 'show'])->name('veterinaires.show');

// Actualités — publiques en lecture, quel que soit le statut de connexion.
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

    /*
    |----------------------------------------------------------------
    | Parcours authentifiés restant sur le layout public (layouts.app)
    |----------------------------------------------------------------
    */
    Route::get('/annonces/{annonce}/commander', [CommandeController::class, 'create'])->name('commandes.create');
    Route::post('/annonces/{annonce}/commander', [CommandeController::class, 'store'])->name('commandes.store');

    Route::get('/mes-commandes/{commande}/paiement', [PaiementController::class, 'show'])->name('paiement.show');
    Route::post('/mes-commandes/{commande}/paiement', [PaiementController::class, 'process'])->name('paiement.process');

    Route::get('/veterinaires/{veterinaire}/rendez-vous', [RendezVousController::class, 'create'])->name('rendez-vous.create');
    Route::post('/veterinaires/{veterinaire}/rendez-vous', [RendezVousController::class, 'store'])->name('rendez-vous.store');

    /*
    |----------------------------------------------------------------
    | Espace connecté — layout à barre latérale (layouts.mon-espace)
    |----------------------------------------------------------------
    */
    Route::prefix('mon-espace')->name('mon-espace.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

        Route::prefix('profil')->name('profil.')->group(function () {
            Route::get('/', [ProfileController::class, 'edit'])->name('edit');
            Route::put('/', [ProfileController::class, 'update'])->name('update');
            Route::put('/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('password');
        });

        // Rédaction d'actualités — tout rôle sauf Acheteur (ActualitePolicy).
        Route::prefix('actualites')->name('actualites.')->group(function () {
            Route::get('/creer', [ActualiteController::class, 'create'])->name('create');
            Route::post('/', [ActualiteController::class, 'store'])->name('store');
            Route::get('/{actualite}/modifier', [ActualiteController::class, 'edit'])->name('edit');
            Route::put('/{actualite}', [ActualiteController::class, 'update'])->name('update');
            Route::delete('/{actualite}', [ActualiteController::class, 'destroy'])->name('destroy');
        });

        // Éleveur / Vendeur de provende / Vendeur d'accessoires : leurs annonces.
        Route::prefix('annonces')->name('annonces.')->group(function () {
            Route::get('/', [AnnonceController::class, 'index'])->name('index');
            Route::get('/creer', [AnnonceController::class, 'create'])->name('create');
            Route::post('/', [AnnonceController::class, 'store'])->name('store');
            Route::get('/{annonce}', [AnnonceController::class, 'show'])->name('show');
            Route::get('/{annonce}/modifier', [AnnonceController::class, 'edit'])->name('edit');
            Route::put('/{annonce}', [AnnonceController::class, 'update'])->name('update');
            Route::delete('/{annonce}', [AnnonceController::class, 'destroy'])->name('destroy');
        });

        // Fournisseur : traitement des commandes reçues sur ses annonces.
        Route::prefix('commandes-recues')->name('commandes-recues.')->group(function () {
            Route::get('/', [CommandeFournisseurController::class, 'index'])->name('index');
            Route::post('/{commande}/prendre-en-charge', [CommandeFournisseurController::class, 'prendreEnCharge'])->name('prendre-en-charge');
            Route::post('/{commande}/valider', [CommandeFournisseurController::class, 'valider'])->name('valider');
            Route::post('/{commande}/refuser', [CommandeFournisseurController::class, 'refuser'])->name('refuser');
        });

        // Acheteur (tout rôle) : suivi de ses propres commandes.
        Route::prefix('mes-commandes')->name('commandes.')->group(function () {
            Route::get('/', [CommandeController::class, 'index'])->name('index');
            Route::get('/{commande}', [CommandeController::class, 'show'])->name('show');
            Route::post('/{commande}/annuler', [CommandeController::class, 'annuler'])->name('annuler');
            Route::post('/{commande}/confirmer-reception', [CommandeController::class, 'confirmerReception'])->name('confirmer-reception');
            Route::post('/{commande}/signaler-probleme', [CommandeController::class, 'signalerProbleme'])->name('signaler-probleme');
            Route::post('/{commande}/noter', [CommandeController::class, 'noter'])->name('noter');
        });

        // Livreur : livraisons proposées et suivi de ses courses.
        Route::prefix('livraisons')->name('livraisons.')->group(function () {
            Route::get('/proposees', [LivraisonController::class, 'proposees'])->name('proposees');
            Route::get('/mes-livraisons', [LivraisonController::class, 'mesLivraisons'])->name('mes');
            Route::get('/{livraison}', [LivraisonController::class, 'show'])->name('show');
            Route::post('/{livraison}/accepter', [LivraisonController::class, 'accepter'])->name('accepter');
            Route::post('/{livraison}/rejeter', [LivraisonController::class, 'rejeter'])->name('rejeter');
            Route::post('/{livraison}/demarrer', [LivraisonController::class, 'demarrer'])->name('demarrer');
            Route::post('/{livraison}/livrer', [LivraisonController::class, 'livrer'])->name('livrer');
        });

        // Vétérinaire : services & tarifs.
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [ServiceVeterinaireController::class, 'index'])->name('index');
            Route::get('/creer', [ServiceVeterinaireController::class, 'create'])->name('create');
            Route::post('/', [ServiceVeterinaireController::class, 'store'])->name('store');
            Route::get('/{service}', [ServiceVeterinaireController::class, 'show'])->name('show');
            Route::get('/{service}/modifier', [ServiceVeterinaireController::class, 'edit'])->name('edit');
            Route::put('/{service}', [ServiceVeterinaireController::class, 'update'])->name('update');
            Route::delete('/{service}', [ServiceVeterinaireController::class, 'destroy'])->name('destroy');
        });

        // Vétérinaire : rendez-vous reçus des éleveurs.
        Route::prefix('rendez-vous-recus')->name('rendez-vous-recus.')->group(function () {
            Route::get('/', [RendezVousController::class, 'recus'])->name('index');
            Route::post('/{rendezVous}/confirmer', [RendezVousController::class, 'confirmer'])->name('confirmer');
            Route::post('/{rendezVous}/refuser', [RendezVousController::class, 'refuser'])->name('refuser');
            Route::post('/{rendezVous}/realise', [RendezVousController::class, 'marquerRealise'])->name('realise');
        });

        // Éleveur : suivi de ses propres demandes de rendez-vous.
        Route::prefix('mes-rendez-vous')->name('rendez-vous.')->group(function () {
            Route::get('/', [RendezVousController::class, 'index'])->name('index');
            Route::get('/{rendezVous}', [RendezVousController::class, 'show'])->name('show');
            Route::post('/{rendezVous}/annuler', [RendezVousController::class, 'annuler'])->name('annuler');
        });

        // Vétérinaire : abonnement Basique / Premium.
        Route::prefix('abonnement')->name('abonnement.')->group(function () {
            Route::get('/', [AbonnementController::class, 'show'])->name('show');
            Route::post('/', [AbonnementController::class, 'souscrire'])->name('souscrire');
        });
    });

    // Espace Administrateur — vue d'ensemble, modération, utilisateurs, litiges.
    // Reste sous son propre préfixe /administration (zone distincte de
    // /mon-espace), mais partage le même layout à barre latérale.
    Route::prefix('administration')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/moderation', [ModerationController::class, 'index'])->name('moderation.index');
        Route::post('/moderation/{annonce}/approuver', [ModerationController::class, 'approuver'])->name('moderation.approuver');
        Route::post('/moderation/{annonce}/rejeter', [ModerationController::class, 'rejeter'])->name('moderation.rejeter');

        Route::get('/utilisateurs', [UserController::class, 'index'])->name('utilisateurs.index');
        Route::get('/utilisateurs/{utilisateur}', [UserController::class, 'show'])->name('utilisateurs.show');
        Route::post('/utilisateurs/{utilisateur}/suspendre', [UserController::class, 'suspendre'])->name('utilisateurs.suspendre');
        Route::post('/utilisateurs/{utilisateur}/reactiver', [UserController::class, 'reactiver'])->name('utilisateurs.reactiver');

        Route::get('/litiges', [LitigeController::class, 'index'])->name('litiges.index');
        Route::post('/litiges/{commande}/faveur-acheteur', [LitigeController::class, 'resoudreEnFaveurAcheteur'])->name('litiges.faveur-acheteur');
        Route::post('/litiges/{commande}/faveur-fournisseur', [LitigeController::class, 'resoudreEnFaveurFournisseur'])->name('litiges.faveur-fournisseur');
    });
});
