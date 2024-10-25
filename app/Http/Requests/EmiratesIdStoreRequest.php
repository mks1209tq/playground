<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmiratesIdStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'max:1'],
            'country_code' => ['required', 'string', 'max:3'],
            'card_number' => ['required', 'string', 'max:10'],
            'id_number' => ['required', 'string', 'max:18'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'string', 'max:6'],
            'expiry_date' => ['required', 'date'],
            'nationality' => ['required', 'string', 'max:3'],
            'surname' => ['required', 'string'],
            'given_names' => ['required', 'string'],
        ];
    }
}
