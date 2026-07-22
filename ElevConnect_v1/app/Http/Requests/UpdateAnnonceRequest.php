<?php

namespace App\Http\Requests;

use App\Models\Annonce;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validation de la modification d'une annonce existante par son propriétaire. */
class UpdateAnnonceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Annonce $annonce */
        $annonce = $this->route('annonce');

        return $this->user() !== null && $this->user()->id_utilisateur === $annonce->id_utilisateur;
    }

    public function rules(): array
    {
        /** @var Annonce $annonce */
        $annonce = $this->route('annonce');

        $rules = [
            'titre' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'prix_unitaire' => ['required', 'numeric', 'min:0'],
            'quantite' => ['required', 'integer', 'min:1'],
            'image_1' => ['nullable', 'image', 'max:4096'],
            'image_2' => ['nullable', 'image', 'max:4096'],
            'etat' => ['required', Rule::in(['disponible', 'stock_epuise'])],
            'reductions' => ['nullable', 'array'],
            'reductions.*.quantite_min' => ['required_with:reductions', 'integer', 'min:1'],
            'reductions.*.quantite_max' => ['required_with:reductions', 'integer', 'gte:reductions.*.quantite_min'],
            'reductions.*.pourcentage_reduction' => ['required_with:reductions', 'numeric', 'min:0', 'max:100'],
        ];

        if ($annonce->type_annonce === 'animal') {
            $rules['poids'] = ['required', 'numeric', 'min:0'];
            $rules['mois'] = ['required', 'integer', 'min:0', 'max:600'];
        } else {
            $rules['unite_de_mesure'] = ['required', Rule::in(['sac', 'kg', 'l', 'bassine', 'autre'])];
        }

        return $rules;
    }
}
