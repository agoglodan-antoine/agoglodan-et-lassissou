<?php

namespace App\Http\Requests;

use App\Models\Abonnement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validation de la souscription à un abonnement vétérinaire (Basique / Premium). */
class SouscrireAbonnementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'veterinaire';
    }

    public function rules(): array
    {
        return [
            'formule' => ['required', Rule::in([Abonnement::BASIQUE, Abonnement::PREMIUM])],
            'moyen_de_paiement' => ['required_if:formule,premium', Rule::in(config('elevconnect.moyens_paiement'))],
            'numero_de_compte' => ['required_if:formule,premium', 'string', 'max:50'],
        ];
    }
}
