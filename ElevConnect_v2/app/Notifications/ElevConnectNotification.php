<?php

namespace App\Notifications;

use App\Notifications\Channels\ElevConnectDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification générique ElevConnect (canaux database + mail), utilisée pour
 * tous les évènements applicatifs : modération d'annonce, commande,
 * livraison, rendez-vous, abonnement... Remplace les écritures directes
 * `NotificationElevConnect::create(...)` par l'API standard Laravel
 * (Illuminate\Notifications\Notification), comme décrit au chapitre 4 du
 * mémoire.
 *
 * Exemple d'utilisation :
 *   $utilisateur->notify(new ElevConnectNotification(
 *       contenu: "Votre annonce « {$annonce->titre} » a été approuvée.",
 *       type: 'annonce_approuvee',
 *       actionText: "Voir l'annonce",
 *       actionUrl: route('catalogue.show', $annonce),
 *   ));
 */
class ElevConnectNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $contenu,
        public ?string $type = null,
        public ?string $actionText = null,
        public ?string $actionUrl = null,
    ) {
    }

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['mail', ElevConnectDatabaseChannel::class];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('ElevConnect — '.$this->sujetCourt())
            ->greeting('Bonjour '.$notifiable->prenom.',')
            ->line($this->contenu);

        if ($this->actionUrl) {
            $message->action($this->actionText ?? 'Voir sur ElevConnect', $this->actionUrl);
        }

        return $message->line('Ceci est une notification automatique, merci de ne pas y répondre.');
    }

    /** Représentation persistée par ElevConnectDatabaseChannel dans `notifications_elevconnect`. */
    public function toDatabaseElevConnect(mixed $notifiable): array
    {
        return [
            'contenu' => $this->contenu,
            'type' => $this->type,
        ];
    }

    public function toArray(mixed $notifiable): array
    {
        return $this->toDatabaseElevConnect($notifiable);
    }

    /** Petit intitulé de sujet d'e-mail, dérivé du type d'évènement. */
    private function sujetCourt(): string
    {
        return match ($this->type) {
            'annonce_approuvee', 'annonce_rejetee' => 'Votre annonce',
            'commande' => 'Votre commande',
            'livraison' => 'Votre livraison',
            'rendez_vous' => 'Votre rendez-vous',
            'abonnement' => 'Votre abonnement',
            default => 'Nouvelle notification',
        };
    }
}
