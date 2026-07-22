<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Profil Livreur : transport sécurisé, planning de disponibilité. */
class Livreur extends Model
{
    protected $table = 'livreurs';
    protected $primaryKey = 'id_utilisateur';
    public $incrementing = false;

    protected $fillable = ['id_utilisateur', 'moyen_transport', 'zone_couverture'];

    public function utilisateur() { return $this->belongsTo(Utilisateur::class, 'id_utilisateur'); }
    public function livraisons() { return $this->hasMany(Livraison::class, 'id_livreur'); }
    public function planning() { return $this->hasMany(PlanningLivreur::class, 'id_livreur'); }
    public function reductions() { return $this->hasMany(ReductionLivraison::class, 'id_livreur'); }

    /**
     * Liste des livreurs actifs les plus proches d'un point donné (le point
     * d'enlèvement, chez le fournisseur), pour que l'acheteur en choisisse un
     * à la commande. Formule de Haversine, cohérente avec CatalogueController.
     */
    public static function candidatsProches(float $lat, float $lng, int $limite = 20)
    {
        return static::query()
            ->join('utilisateurs', 'utilisateurs.id_utilisateur', '=', 'livreurs.id_utilisateur')
            ->where('utilisateurs.statut', 'actif')
            ->whereNotNull('utilisateurs.latitude')
            ->whereNotNull('utilisateurs.longitude')
            ->selectRaw('livreurs.*, utilisateurs.nom, utilisateurs.prenom, (6371 * acos(
                    cos(radians(?)) * cos(radians(utilisateurs.latitude)) * cos(radians(utilisateurs.longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(utilisateurs.latitude))
                )) as distance_km', [$lat, $lng, $lat])
            ->orderBy('distance_km')
            ->limit($limite)
            ->get();
    }
}
