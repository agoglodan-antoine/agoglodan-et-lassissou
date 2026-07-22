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
     * Vrai si le livreur n'a défini aucun créneau d'indisponibilité couvrant
     * l'instant présent — c'est-à-dire s'il est en mesure d'accepter une
     * livraison maintenant, selon son planning de disponibilité (voir
     * PlanningLivreur). Un livreur sans créneau déclaré est considéré
     * disponible par défaut (fonctionnalité facultative, pas une contrainte
     * bloquante pour ceux qui ne l'utilisent pas).
     */
    public function estDisponibleMaintenant(): bool
    {
        return ! $this->planning()
            ->where('disponible', false)
            ->where('date_debut', '<=', now())
            ->where('date_fin', '>=', now())
            ->exists();
    }

    /**
     * Liste des livreurs actifs les plus proches d'un point donné (le point
     * d'enlèvement, chez le fournisseur), pour que l'acheteur en choisisse un
     * à la commande. Formule de Haversine, cohérente avec CatalogueController.
     * Ne retient que les livreurs actuellement disponibles selon leur
     * planning (cf. estDisponibleMaintenant()).
     *
     * $recherche filtre en plus par nom/prénom (recherche AJAX d'un livreur
     * précis) ; laissé à null pour la simple liste "les plus proches".
     */
    public static function candidatsProches(float $lat, float $lng, int $limite = 20, ?string $recherche = null)
    {
        return static::query()
            ->join('utilisateurs', 'utilisateurs.id_utilisateur', '=', 'livreurs.id_utilisateur')
            ->where('utilisateurs.statut', 'actif')
            ->whereNotNull('utilisateurs.latitude')
            ->whereNotNull('utilisateurs.longitude')
            ->when($recherche, function ($query) use ($recherche) {
                $query->where(function ($q) use ($recherche) {
                    $q->where('utilisateurs.nom', 'like', "%{$recherche}%")
                        ->orWhere('utilisateurs.prenom', 'like', "%{$recherche}%");
                });
            })
            ->selectRaw('livreurs.*, utilisateurs.nom, utilisateurs.prenom, (6371 * acos(
                    cos(radians(?)) * cos(radians(utilisateurs.latitude)) * cos(radians(utilisateurs.longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(utilisateurs.latitude))
                )) as distance_km', [$lat, $lng, $lat])
            ->orderBy('distance_km')
            ->get()
            ->filter(fn (self $livreur) => $livreur->estDisponibleMaintenant())
            ->take($limite)
            ->values();
    }
}
