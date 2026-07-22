<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommandeRequest;
use App\Models\Annonce;
use App\Models\Commande;
use App\Models\Livreur;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Commandes passées par l'Acheteur sur une annonce du catalogue.
 * Le paiement (séquestre) est traité séparément par PaiementController —
 * une commande créée ici reste `en_attente` tant qu'elle n'est pas payée.
 *
 * Choix du mode de réception (cahier des charges, tableau 2.4/2.5) : à la
 * commande, l'acheteur choisit soit un livreur précis (proposé selon la
 * proximité avec le fournisseur), soit un retrait direct sans livreur —
 * l'intervention d'un livreur reste optionnelle.
 */
class CommandeController extends Controller
{
    public function create(Request $request, Annonce $annonce): View
    {
        $this->authorize('create', Commande::class);

        abort_unless($annonce->statut === Annonce::STATUT_VISIBLE, 404);
        abort_if($annonce->id_utilisateur === $request->user()->id_utilisateur, 403,
            "Vous ne pouvez pas commander votre propre annonce.");

        $annonce->load('reductions', 'auteur');

        $livreurs = collect();
        if ($annonce->auteur->latitude && $annonce->auteur->longitude) {
            $livreurs = Livreur::candidatsProches($annonce->auteur->latitude, $annonce->auteur->longitude, 5);
        }

        // Barème de réduction pré-calculé côté serveur, sous forme de tableau
        // PHP simple : @json() sur une expression chaînée (->map(fn...)) est
        // fragile côté Blade (comptage des parenthèses/crochets imbriqués),
        // on évite donc de faire ce calcul directement dans la vue.
        $tranchesReduction = [];
        foreach ($annonce->reductions as $reduction) {
            $tranchesReduction[] = [
                'min' => (int) $reduction->quantite_min,
                'max' => (int) $reduction->quantite_max,
                'pct' => (float) $reduction->pourcentage_reduction,
            ];
        }

        return view('commandes.create', compact('annonce', 'livreurs', 'tranchesReduction'));
    }

    /**
     * Recherche AJAX d'un livreur par nom/prénom, triée par proximité avec
     * le fournisseur de l'annonce — alimente le champ de recherche de la
     * fenêtre de choix du livreur (resources/views/commandes/create.blade.php).
     */
    public function rechercherLivreurs(Request $request, Annonce $annonce): \Illuminate\Http\JsonResponse
    {
        $annonce->load('auteur');

        if (! $annonce->auteur->latitude || ! $annonce->auteur->longitude) {
            return response()->json(['livreurs' => []]);
        }

        $q = trim((string) $request->query('q', ''));

        $livreurs = Livreur::candidatsProches($annonce->auteur->latitude, $annonce->auteur->longitude, 10, $q ?: null);

        return response()->json([
            'livreurs' => $livreurs->map(fn (Livreur $l) => [
                'id' => $l->id_utilisateur,
                'nom' => $l->nom,
                'prenom' => $l->prenom,
                'distance_km' => round($l->distance_km, 1),
                'moyen_transport' => $l->moyen_transport,
            ])->values(),
        ]);
    }

    public function store(StoreCommandeRequest $request, Annonce $annonce): RedirectResponse
    {
        abort_unless($annonce->statut === Annonce::STATUT_VISIBLE, 404);
        abort_if($annonce->id_utilisateur === $request->user()->id_utilisateur, 403,
            "Vous ne pouvez pas commander votre propre annonce.");

        $data = $request->validated();

        $annonce->load('reductions');
        $montant = $annonce->calculerMontant($data['quantite']);

        $commande = Commande::create([
            'id_annonce' => $annonce->id_annonce,
            'id_acheteur' => $request->user()->id_utilisateur,
            'quantite' => $data['quantite'],
            'prix_unitaire' => $annonce->prix_unitaire,
            'montant_total' => $montant['montant_total'],
            'reduction_sur_commande' => $montant['reduction_sur_commande'],
            'montant_net_commande' => $montant['montant_net_commande'],
            'statut' => Commande::EN_ATTENTE,
            'code_authenticite' => Commande::genererCodeAuthenticite(),
            'id_livreur_souhaite' => $data['mode_reception'] === 'livreur' ? $data['id_livreur'] : null,
            'date_commande' => now(),
        ]);

        return redirect()->route('paiement.show', $commande)
            ->with('status', 'Commande créée. Réglez-la en ligne pour la confirmer.');
    }

    public function index(Request $request): View
    {
        $commandes = $request->user()->commandes()
            ->with('annonce')
            ->latest('date_commande')
            ->paginate(12);

        return view('commandes.index', compact('commandes'));
    }

    public function show(Commande $commande): View
    {
        $this->authorize('view', $commande);

        $commande->load(['annonce.auteur', 'paiement', 'livraison.livreur']);

        return view('commandes.show', compact('commande'));
    }

    public function annuler(Commande $commande): RedirectResponse
    {
        $this->authorize('annuler', $commande);

        $commande->update(['statut' => Commande::ANNULEE]);

        return redirect()->route('mon-espace.commandes.index')->with('status', 'Commande annulée.');
    }

    /**
     * Confirmation de réception par l'acheteur.
     * - Avec livreur : le code saisi (issu du scan du QR code) doit
     *   correspondre exactement au `code_authenticite` de la commande.
     * - Retrait direct : aucune vérification par QR code, celle-ci n'ayant
     *   de sens qu'en présence d'un livreur intermédiaire — l'acheteur
     *   confirme simplement la réception en main propre auprès du fournisseur.
     * Dans les deux cas, le succès déclenche les versements au fournisseur
     * et, le cas échéant, au livreur.
     */
    public function confirmerReception(Request $request, Commande $commande): RedirectResponse
    {
        $this->authorize('view', $commande);

        $statutsAutorises = $commande->estRetraitDirect()
            ? [Commande::VALIDEE, Commande::LIVREE]
            : [Commande::LIVREE];

        abort_unless(in_array($commande->statut, $statutsAutorises, true), 422,
            $commande->estRetraitDirect()
                ? "Cette commande n'est pas encore validée par le fournisseur."
                : "Cette commande n'est pas encore au statut « livrée »."
        );

        if (! $commande->estRetraitDirect()) {
            $data = $request->validate(['code' => ['required', 'string']]);

            try {
                $codeDechiffre = Crypt::decryptString(trim($data['code']));
            } catch (DecryptException $e) {
                $codeDechiffre = null;
            }

            if ($codeDechiffre === null || ! hash_equals($commande->code_authenticite, $codeDechiffre)) {
                return back()->withErrors(['code' =>
                    "Code invalide. Réessayez en scannant le QR code affiché par le livreur, ou cette commande n'est peut-être pas destinée à vous."
                ]);
            }
        }

        DB::transaction(function () use ($commande) {
            $commande->load('livraison', 'paiement');

            $commande->update(['statut' => Commande::CONFIRMEE]);

            if ($commande->livraison) {
                $commande->livraison->update([
                    'statut' => \App\Models\Livraison::STATUT_TERMINEE,
                    'verification_authenticite' => 'verifiee',
                    'date_verification_qr' => now(),
                ]);
            }

            (new VersementController)->creerPourCommande($commande);
        });

        return back()->with('status', 'Réception confirmée. Le fournisseur (et le livreur) ont été payés.');
    }

    /** L'acheteur signale un problème après réception — ouvre un litige traité en Phase 6. */
    public function signalerProbleme(Request $request, Commande $commande): RedirectResponse
    {
        $this->authorize('view', $commande);

        $statutsAutorises = $commande->estRetraitDirect()
            ? [Commande::VALIDEE, Commande::LIVREE]
            : [Commande::LIVREE];

        abort_unless(in_array($commande->statut, $statutsAutorises, true), 422);

        $data = $request->validate(['description' => ['required', 'string', 'max:1000']]);

        $commande->update(['statut' => Commande::EN_LITIGE, 'description' => $data['description']]);

        return back()->with('status', "Litige signalé. Notre équipe reviendra vers vous (module Administration, Phase 6).");
    }

    /** Avis de l'acheteur sur la commande (fournisseur) et, le cas échéant, la livraison. */
    public function noter(Request $request, Commande $commande): RedirectResponse
    {
        $this->authorize('view', $commande);

        abort_unless($commande->statut === Commande::CONFIRMEE, 422);

        $data = $request->validate([
            'note_client_commande' => ['required', 'integer', 'min:1', 'max:5'],
            'avis_client_commande' => ['nullable', 'string', 'max:1000'],
            'note_client_livraison' => ['nullable', 'integer', 'min:1', 'max:5'],
            'avis_client_livraison' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($commande, $data) {
            $commande->update([
                'note_client_commande' => $data['note_client_commande'],
                'avis_client_commande' => $data['avis_client_commande'] ?? null,
            ]);

            $this->mettreAJourNoteMoyenneFournisseur($commande);

            $commande->load('livraison');
            if ($commande->livraison && isset($data['note_client_livraison'])) {
                $commande->livraison->update([
                    'note_client_livraison' => $data['note_client_livraison'],
                    'avis_client_livraison' => $data['avis_client_livraison'] ?? null,
                ]);
            }
        });

        return back()->with('status', 'Merci pour votre avis !');
    }

    /**
     * Recalcule la note moyenne du fournisseur (Éleveur / Vendeur de provende /
     * Vendeur d'accessoires) à partir de l'ensemble des commandes notées sur
     * ses annonces.
     */
    private function mettreAJourNoteMoyenneFournisseur(Commande $commande): void
    {
        $commande->load('annonce.auteur');
        $profil = $commande->annonce->auteur->profil();

        if (! $profil || ! method_exists($profil, 'getAttribute')) {
            return;
        }

        $stats = Commande::whereHas('annonce', function ($q) use ($commande) {
                $q->where('id_utilisateur', $commande->annonce->id_utilisateur);
            })
            ->whereNotNull('note_client_commande')
            ->selectRaw('AVG(note_client_commande) as moyenne, COUNT(*) as total')
            ->first();

        $profil->update([
            'note_moyenne' => round($stats->moyenne, 2),
            'nombre_avis' => $stats->total,
        ]);
    }
}
