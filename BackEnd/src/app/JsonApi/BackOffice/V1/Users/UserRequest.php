<?php

namespace App\JsonApi\BackOffice\V1\Users;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class UserRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {

        $data = $this->model();
        $uniqueEmail = Rule::unique('users', 'email');
        $required =  'required';
        $requiredNullable =  'required';

        if ($data) {
            $uniqueEmail->ignoreModel($data);
            $required =  'required_if:active,=,null';
            $requiredNullable = 'nullable';
            
        }

        return [
            // @TODO
            'name' => ['required', 'string'],
            'email' => ['required', 'email', $uniqueEmail],
            'password' => [$requiredNullable, 'string','same:password_confirmation'],
            'password_confirmation' => ['required_with:password','nullable', 'string'],
            'roles' => [$requiredNullable, 'array'],
            'roles.*' => ['required', 'uuid', 'exists:roles,id'],
            'active' => ['nullable', 'boolean'],
        ];
    }

}
