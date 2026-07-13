<?php

namespace App\Http\Requests;

use App\Models\Annonce;
use App\Models\Utilisateur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation de la publication d'une annonce.
 * Le type d'annonce n'est PAS choisi par le fournisseur : il est déduit de
 * son rôle (règle de gestion — voir trg_annonces_type_role dans le schéma SQL
 * et Annonce::typeAttenduPourRole()).
 */
class StoreAnnonceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->estFournisseur();
    }

    public function rules(): array
    {
        $type = Annonce::typeAttenduPourRole($this->user()->role);

        $rules = [
            'titre' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'prix_unitaire' => ['required', 'numeric', 'min:0'],
            'quantite' => ['required', 'integer', 'min:1'],
            'image_1' => ['required', 'image', 'max:4096'],
            'image_2' => ['nullable', 'image', 'max:4096'],
            'reductions' => ['nullable', 'array'],
            'reductions.*.quantite_min' => ['required_with:reductions', 'integer', 'min:1'],
            'reductions.*.quantite_max' => ['required_with:reductions', 'integer', 'gte:reductions.*.quantite_min'],
            'reductions.*.pourcentage_reduction' => ['required_with:reductions', 'numeric', 'min:0', 'max:100'],
        ];

        if ($type === 'animal') {
            $rules['poids'] = ['required', 'numeric', 'min:0'];
            $rules['mois'] = ['required', 'integer', 'min:0', 'max:600'];
        } else {
            $rules['unite_de_mesure'] = ['required', Rule::in(['sac', 'kg', 'l', 'bassine', 'autre'])];
        }

        return $rules;
    }

    /** Type déduit du rôle du fournisseur connecté — jamais fourni par le formulaire. */
    public function typeAnnonce(): string
    {
        return Annonce::typeAttenduPourRole($this->user()->role);
    }
}
