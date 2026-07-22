<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validation de l'ajout d'un créneau d'indisponibilité au planning du livreur. */
class StorePlanningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'livreur';
    }

    public function rules(): array
    {
        return [
            'date_debut' => ['required', 'date', 'after_or_equal:now'],
            'date_fin' => ['required', 'date', 'after:date_debut'],
        ];
    }
}
