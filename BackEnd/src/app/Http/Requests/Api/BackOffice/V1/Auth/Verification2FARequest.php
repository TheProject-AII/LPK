<?php

namespace App\Http\Requests\Api\BackOffice\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Verification2FARequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'data.attributes.email' => ['required', 'email'],
            'data.attributes.token' => ['required','string']
        ];
    }
 
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'data.attributes.email.required' => 'The email field is required.',
            'data.attributes.email.email' => 'The email field must be a valid email address.',
            'data.attributes.token.required' => 'The token field is required.',
        ];
    }
}
