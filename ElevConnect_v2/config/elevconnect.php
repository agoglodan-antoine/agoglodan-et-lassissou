<?php

/**
 * Paramètres métier d'ElevConnect, centralisés pour éviter les valeurs
 * "magiques" éparpillées dans le code (cf. cahier des charges, chap. 2-3).
 *
 * IMPORTANT — voir README_ROADMAP.md, section "Écarts corrigés" :
 * le mémoire (chap. 2, 3 et le résumé) fixe la commission à 5 %, alors que
 * les commentaires du script SQL v4 (PAIEMENTS.commission_sur_commande)
 * mentionnent par erreur 2 %. Le texte du cahier des charges fait foi ; la
 * valeur retenue ici est donc 0.05 (5 %).
 */
return [

    // Commission ElevConnect prélevée sur le montant net de chaque commande
    // ET sur le montant net de chaque livraison, lors du versement.
    'commission_rate' => 0.05,

    // Abonnement réservé aux vétérinaires (seul rôle éligible).
    'abonnement_veterinaire' => [
        'basique' => [
            'label' => 'Basique',
            'prix_mensuel' => 0,
            'services_limites' => true,
            // Le mémoire indique des "services limités" en formule Basique sans fixer de
            // chiffre précis : 3 services actifs simultanément est une valeur par défaut
            // assumée ici, modifiable sans toucher au code (voir README_ROADMAP.md, Phase 5).
            'limite_services' => 3,
        ],
        'premium' => [
            'label' => 'Premium',
            'prix_mensuel' => 2000, // FCFA / mois
            'services_limites' => false,
            'avantages' => [
                'Services illimités',
                'Mise en avant du profil',
                'Statistiques détaillées',
                'Support prioritaire',
            ],
        ],
    ],

    // Moyens de paiement / versement supportés (passerelle Mobile Money / carte bancaire).
    'moyens_paiement' => ['mobile_money', 'carte_bancaire'],

];
