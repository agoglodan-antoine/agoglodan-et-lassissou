<?php

namespace App\Http\Requests;

use App\Models\RendezVous;
use Illuminate\Foundation\Http\FormRequest;

/** Validation de la prise de rendez-vous auprès d'un vétérinaire. */
class StoreRendezVousRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', RendezVous::class);
    }

    public function rules(): array
    {
        return [
            'id_service' => ['nullable', 'exists:services_veterinaires,id_service'],
            'sujet' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'date_prevue' => ['required', 'date', 'after:now'],
        ];
    }
}
