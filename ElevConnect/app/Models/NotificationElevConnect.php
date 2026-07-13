<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Notification système (in-app + email) rattachée à un utilisateur. */
class NotificationElevConnect extends Model
{
    protected $table = 'notifications_elevconnect';
    protected $primaryKey = 'id_notification';

    protected $fillable = ['id_utilisateur', 'contenu', 'type', 'lu', 'date_creation'];

    protected function casts(): array
    {
        return ['lu' => 'boolean', 'date_creation' => 'datetime'];
    }

    public function utilisateur() { return $this->belongsTo(Utilisateur::class, 'id_utilisateur'); }
}
