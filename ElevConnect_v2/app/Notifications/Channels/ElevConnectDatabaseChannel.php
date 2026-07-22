<?php

namespace App\Notifications\Channels;

use App\Models\NotificationElevConnect;
use Illuminate\Notifications\Notification;

/**
 * Canal "database" maison : au lieu d'écrire dans la table `notifications`
 * native de Laravel, persiste dans `notifications_elevconnect` — la table du
 * cahier des charges, déjà utilisée par le centre de notifications
 * (NotificationController, resources/views/notifications/index.blade.php).
 *
 * Permet d'utiliser l'API standard Illuminate\Notifications\Notification
 * (::notify(), via(), toDatabase()...) tout en conservant le schéma métier
 * existant plutôt que la table générique de Laravel.
 */
class ElevConnectDatabaseChannel
{
    public function send(mixed $notifiable, Notification $notification): NotificationElevConnect
    {
        $data = method_exists($notification, 'toDatabaseElevConnect')
            ? $notification->toDatabaseElevConnect($notifiable)
            : $notification->toArray($notifiable);

        return NotificationElevConnect::create([
            'id_utilisateur' => $notifiable->id_utilisateur,
            'contenu' => $data['contenu'],
            'type' => $data['type'] ?? null,
            'lu' => false,
            'date_creation' => now(),
        ]);
    }
}
