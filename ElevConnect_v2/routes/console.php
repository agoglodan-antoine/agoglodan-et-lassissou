<?php

use App\Console\Commands\VerifierExpirationAbonnements;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tâches planifiées
|--------------------------------------------------------------------------
|
| Le mémoire (chap. 4, §gestion des abonnements) prévoit une tâche planifiée
| quotidienne qui fait passer un abonnement Premium arrivé à expiration au
| statut "expire" (et donc, implicitement, le vétérinaire repasse en
| formule Basique — voir Veterinaire::estPremium()).
|
| En local, exécuter le "scheduler worker" Laravel pour que cette tâche
| tourne réellement : `php artisan schedule:work`.
| En production, une entrée cron unique suffit :
|   * * * * * php /chemin/vers/le/projet/artisan schedule:run >> /dev/null 2>&1
|
*/

Schedule::command(VerifierExpirationAbonnements::class)->daily();
