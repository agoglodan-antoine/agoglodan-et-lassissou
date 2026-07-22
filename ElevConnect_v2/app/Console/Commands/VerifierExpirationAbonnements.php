<?php

namespace App\Console\Commands;

use App\Models\Abonnement;
use App\Models\Utilisateur;
use App\Notifications\ElevConnectNotification;
use Illuminate\Console\Command;

/**
 * Fait passer au statut "expire" tout abonnement encore marqué "actif" dont
 * la date_expiration est dépassée. Sans cette tâche, Veterinaire::estPremium()
 * masquait le problème en revérifiant la date à la volée, mais la ligne
 * restait "actif" en base indéfiniment (voir CONFORMITE_MEMOIRE.md).
 *
 * Planifiée quotidiennement dans routes/console.php :
 *   Schedule::command('abonnements:verifier-expiration')->daily();
 */
class VerifierExpirationAbonnements extends Command
{
    protected $signature = 'abonnements:verifier-expiration';

    protected $description = "Fait expirer les abonnements vétérinaires dont la date d'expiration est dépassée";

    public function handle(): int
    {
        $abonnementsExpires = Abonnement::where('statut', 'actif')
            ->whereDate('date_expiration', '<', now()->toDateString())
            ->get();

        foreach ($abonnementsExpires as $abonnement) {
            $abonnement->update(['statut' => 'expire']);

            if ($abonnement->formule === Abonnement::PREMIUM) {
                Utilisateur::find($abonnement->id_veterinaire)?->notify(new ElevConnectNotification(
                    contenu: 'Votre abonnement Premium a expiré. Vous êtes repassé à la formule Basique — renouvelez-le depuis votre espace pour conserver vos avantages.',
                    type: 'abonnement',
                    actionText: 'Renouveler mon abonnement',
                    actionUrl: route('mon-espace.abonnement.show'),
                ));
            }
        }

        $this->info(sprintf('%d abonnement(s) expiré(s) traité(s).', $abonnementsExpires->count()));

        return self::SUCCESS;
    }
}
