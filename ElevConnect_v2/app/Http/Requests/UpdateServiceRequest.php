<?php

namespace App\Http\Requests;

use App\Models\ServiceVeterinaire;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validation de la mise à jour d'un service vétérinaire. */
class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ServiceVeterinaire $service */
        $service = $this->route('service');

        return $this->user() !== null && $this->user()->can('update', $service);
    }

    public function rules(): array
    {
        return [
            'titre_service' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'prix' => ['required', 'numeric', 'min:0'],
            'temps_traitement' => ['required', 'integer', 'min:5', 'max:600'],
            'statut_service' => ['required', Rule::in(['disponible', 'indisponible'])],
            'photo_illustrative' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
