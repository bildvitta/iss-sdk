<?php

namespace BildVitta\Hub\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MePatchRequest extends FormRequest
{
    public function rules(): array
    {
        $companyTable = app(config('hub.model_company'))->getTable();

        return [
            'companies' => [
                'sometimes', 'uuid', "exists:{$companyTable},uuid",
            ],
            'current_main_company' => [
                'sometimes', 'uuid', "exists:{$companyTable},uuid",
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
