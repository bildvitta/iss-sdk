<?php

namespace BildVitta\Hub\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MePatchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'companies' => ['required', 'uuid'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
