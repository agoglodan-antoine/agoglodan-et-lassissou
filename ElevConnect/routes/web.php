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
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\CommandeFournisseurController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LivraisonController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceVeterinaireController;
use App\Http\Controllers\VeterinaireController;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes web
|--------------------------------------------------------------------------
| Phase 1 : accueil, inscription différenciée par rôle, connexion.
| Phase 2 : catalogue public + gestion des annonces par le fournisseur +
|           file de modération administrateur.
| Phase 3 : commandes acheteur, paiement en ligne (séquestre + code QR),
|           traitement des commandes côté fournisseur.
| Phase 4 : livraisons (espace livreur), confirmation de réception par QR,
|           versements, avis.
| Phase 5 : annuaire vétérinaires, services & tarifs, rendez-vous, abonnements.
| Phase 6 : administration (utilisateurs, litiges), actualités, notifications.
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

    // Centre de notifications, commun à tous les rôles.
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // Tableau de bord personnel : vue d'ensemble adaptée au rôle (DashboardController).
    // L'Administrateur est redirigé vers sa propre vue d'ensemble (plus riche),
    // gérée directement dans DashboardController::index().
    Route::get('/tableau-de-bord', [DashboardController::class, 'index'])->name('dashboard');

    // Rédaction d'actualités — tout rôle sauf Acheteur (AnnoncePolicy-like gate).
    Route::get('/actualites/creer', [ActualiteController::class, 'create'])->name('actualites.create');
    Route::post('/actualites', [ActualiteController::class, 'store'])->name('actualites.store');
    Route::get('/actualites/{actualite}/modifier', [ActualiteController::class, 'edit'])->name('actualites.edit');
    Route::put('/actualites/{actualite}', [ActualiteController::class, 'update'])->name('actualites.update');
    Route::delete('/actualites/{actualite}', [ActualiteController::class, 'destroy'])->name('actualites.destroy');

    // Espace fournisseur (Éleveur / Vendeur de provende / Vendeur d'accessoires) :
    // gestion de ses propres annonces. L'autorisation fine (rôle fournisseur,
    // propriété de l'annonce) est portée par AnnoncePolicy + $this->authorize().
    Route::prefix('mon-espace')->name('annonces.')->group(function () {
        Route::get('/annonces', [AnnonceController::class, 'index'])->name('index');
        Route::get('/annonces/creer', [AnnonceController::class, 'create'])->name('create');
        Route::post('/annonces', [AnnonceController::class, 'store'])->name('store');
        Route::get('/annonces/{annonce}/modifier', [AnnonceController::class, 'edit'])->name('edit');
        Route::put('/annonces/{annonce}', [AnnonceController::class, 'update'])->name('update');
        Route::delete('/annonces/{annonce}', [AnnonceController::class, 'destroy'])->name('destroy');
    });

    // Espace fournisseur : traitement des commandes reçues sur ses annonces.
    Route::prefix('mon-espace')->name('commandes-fournisseur.')->group(function () {
        Route::get('/commandes', [CommandeFournisseurController::class, 'index'])->name('index');
        Route::post('/commandes/{commande}/prendre-en-charge', [CommandeFournisseurController::class, 'prendreEnCharge'])->name('prendre-en-charge');
        Route::post('/commandes/{commande}/valider', [CommandeFournisseurController::class, 'valider'])->name('valider');
        Route::post('/commandes/{commande}/refuser', [CommandeFournisseurController::class, 'refuser'])->name('refuser');
    });

    // Espace acheteur : passage de commande et paiement en séquestre.
    Route::get('/annonces/{annonce}/commander', [CommandeController::class, 'create'])->name('commandes.create');
    Route::post('/annonces/{annonce}/commander', [CommandeController::class, 'store'])->name('commandes.store');
    Route::get('/mes-commandes', [CommandeController::class, 'index'])->name('commandes.index');
    Route::get('/mes-commandes/{commande}', [CommandeController::class, 'show'])->name('commandes.show');
    Route::post('/mes-commandes/{commande}/annuler', [CommandeController::class, 'annuler'])->name('commandes.annuler');
    Route::post('/mes-commandes/{commande}/confirmer-reception', [CommandeController::class, 'confirmerReception'])->name('commandes.confirmer-reception');
    Route::post('/mes-commandes/{commande}/signaler-probleme', [CommandeController::class, 'signalerProbleme'])->name('commandes.signaler-probleme');
    Route::post('/mes-commandes/{commande}/noter', [CommandeController::class, 'noter'])->name('commandes.noter');

    Route::get('/mes-commandes/{commande}/paiement', [PaiementController::class, 'show'])->name('paiement.show');
    Route::post('/mes-commandes/{commande}/paiement', [PaiementController::class, 'process'])->name('paiement.process');

    // Espace Livreur : livraisons qui lui sont proposées (assignées par l'acheteur) et suivi des livraisons acceptées.
    Route::prefix('mon-espace')->name('livraison.')->group(function () {
        Route::get('/livraisons-proposees', [LivraisonController::class, 'proposees'])->name('proposees');
        Route::get('/mes-livraisons', [LivraisonController::class, 'mesLivraisons'])->name('mes');
        Route::post('/livraisons/{livraison}/accepter', [LivraisonController::class, 'accepter'])->name('accepter');
        Route::post('/livraisons/{livraison}/rejeter', [LivraisonController::class, 'rejeter'])->name('rejeter');
        Route::post('/livraisons/{livraison}/demarrer', [LivraisonController::class, 'demarrer'])->name('demarrer');
        Route::post('/livraisons/{livraison}/livrer', [LivraisonController::class, 'livrer'])->name('livrer');
    });

    // Espace Vétérinaire : services & tarifs, rendez-vous reçus, abonnement.
    Route::prefix('mon-espace')->name('services.')->group(function () {
        Route::get('/services', [ServiceVeterinaireController::class, 'index'])->name('index');
        Route::get('/services/creer', [ServiceVeterinaireController::class, 'create'])->name('create');
        Route::post('/services', [ServiceVeterinaireController::class, 'store'])->name('store');
        Route::get('/services/{service}/modifier', [ServiceVeterinaireController::class, 'edit'])->name('edit');
        Route::put('/services/{service}', [ServiceVeterinaireController::class, 'update'])->name('update');
        Route::delete('/services/{service}', [ServiceVeterinaireController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('mon-espace')->name('rendez-vous-recus.')->group(function () {
        Route::get('/rendez-vous', [RendezVousController::class, 'recus'])->name('index');
        Route::post('/rendez-vous/{rendezVous}/confirmer', [RendezVousController::class, 'confirmer'])->name('confirmer');
        Route::post('/rendez-vous/{rendezVous}/refuser', [RendezVousController::class, 'refuser'])->name('refuser');
        Route::post('/rendez-vous/{rendezVous}/realise', [RendezVousController::class, 'marquerRealise'])->name('realise');
    });

    Route::prefix('mon-espace')->name('abonnement.')->group(function () {
        Route::get('/abonnement', [AbonnementController::class, 'show'])->name('show');
        Route::post('/abonnement', [AbonnementController::class, 'souscrire'])->name('souscrire');
    });

    // Espace Éleveur : prise de rendez-vous auprès d'un vétérinaire.
    Route::get('/veterinaires/{veterinaire}/rendez-vous', [RendezVousController::class, 'create'])->name('rendez-vous.create');
    Route::post('/veterinaires/{veterinaire}/rendez-vous', [RendezVousController::class, 'store'])->name('rendez-vous.store');
    Route::get('/mes-rendez-vous', [RendezVousController::class, 'index'])->name('rendez-vous.index');
    Route::post('/mes-rendez-vous/{rendezVous}/annuler', [RendezVousController::class, 'annuler'])->name('rendez-vous.annuler');

    // Espace Administrateur : vue d'ensemble, modération des annonces (Phase 2),
    // gestion des utilisateurs et traitement des litiges (Phase 6).
    Route::prefix('administration')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/moderation', [ModerationController::class, 'index'])->name('moderation.index');
        Route::post('/moderation/{annonce}/approuver', [ModerationController::class, 'approuver'])->name('moderation.approuver');
        Route::post('/moderation/{annonce}/rejeter', [ModerationController::class, 'rejeter'])->name('moderation.rejeter');

        Route::get('/utilisateurs', [UserController::class, 'index'])->name('utilisateurs.index');
        Route::post('/utilisateurs/{utilisateur}/suspendre', [UserController::class, 'suspendre'])->name('utilisateurs.suspendre');
        Route::post('/utilisateurs/{utilisateur}/reactiver', [UserController::class, 'reactiver'])->name('utilisateurs.reactiver');

        Route::get('/litiges', [LitigeController::class, 'index'])->name('litiges.index');
        Route::post('/litiges/{commande}/faveur-acheteur', [LitigeController::class, 'resoudreEnFaveurAcheteur'])->name('litiges.faveur-acheteur');
        Route::post('/litiges/{commande}/faveur-fournisseur', [LitigeController::class, 'resoudreEnFaveurFournisseur'])->name('litiges.faveur-fournisseur');
    });
});
