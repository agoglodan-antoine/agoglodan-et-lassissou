<?php

namespace App\Http\Requests;

use App\Models\Commande;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validation du passage de commande sur une annonce du catalogue. */
class StoreCommandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', Commande::class);
    }

    public function rules(): array
    {
        /** @var \App\Models\Annonce $annonce */
        $annonce = $this->route('annonce');

        return [
            'quantite' => ['required', 'integer', 'min:1', 'max:'.$annonce->quantite],
            'mode_reception' => ['required', Rule::in(['retrait_direct', 'livreur'])],
            'id_livreur' => ['required_if:mode_reception,livreur', 'nullable', 'exists:livreurs,id_utilisateur'],
        ];
    }
}
