<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Commande passée sur une annonce.
 * Cycle de vie complet documenté au chapitre 3 du mémoire (diagramme d'états).
 */
class Commande extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'commandes';
    protected $primaryKey = 'id_commande';

    const EN_ATTENTE = 'en_attente';
    const PAYEE = 'payee';
    const EN_COURS_DE_TRAITEMENT = 'en_cours_de_traitement';
    const ANNULEE = 'annulee';
    const VALIDEE = 'validee';
    const EN_COURS_DE_LIVRAISON = 'en_cours_de_livraison';
    const LIVREE = 'livree';
    const CONFIRMEE = 'confirmee';
    const REFUSEE = 'refusee';
    const EN_LITIGE = 'en_litige';

    protected $fillable = [
        'id_annonce', 'id_acheteur', 'quantite', 'prix_unitaire', 'montant_total',
        'reduction_sur_commande', 'montant_net_commande', 'statut', 'motif_de_rejet',
        'description', 'note_client_commande', 'avis_client_commande', 'code_authenticite',
        'date_commande',
    ];

    protected function casts(): array
    {
        return ['date_commande' => 'datetime'];
    }

    public function annonce() { return $this->belongsTo(Annonce::class, 'id_annonce'); }
    public function acheteur() { return $this->belongsTo(Utilisateur::class, 'id_acheteur'); }
    public function paiement() { return $this->hasOne(Paiement::class, 'id_commande'); }
    public function livraison() { return $this->hasOne(Livraison::class, 'id_commande'); }
    public function versements() { return $this->hasMany(Versement::class, 'id_commande'); }

    /** Génère un code d'authenticité unique destiné au QR code de vérification à la livraison. */
    public static function genererCodeAuthenticite(): string
    {
        return strtoupper(bin2hex(random_bytes(16)));
    }
}
