<?php

namespace BildVitta\Hub\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class LogoutRequest
 */
class LogoutRequest extends FormRequest
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
        return [];
    }
}
