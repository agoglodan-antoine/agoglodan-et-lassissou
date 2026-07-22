<?php

namespace App\Http\Requests;

use App\Models\ServiceVeterinaire;
use Illuminate\Foundation\Http\FormRequest;

/** Validation de la publication d'un service vétérinaire. */
class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', ServiceVeterinaire::class);
    }

    public function rules(): array
    {
        return [
            'titre_service' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'prix' => ['required', 'numeric', 'min:0'],
            'temps_traitement' => ['required', 'integer', 'min:5', 'max:600'],
            'photo_illustrative' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
