<?php

namespace BildVitta\Hub\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MePatchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'companies' => [
                'sometimes', 'uuid', 'exists:companies,uuid',
            ],
            'current_main_company' => [
                'sometimes', 'uuid', 'exists:companies,uuid',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
