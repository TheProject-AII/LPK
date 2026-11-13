<?php

namespace App\JsonApi\BackOffice\V1\Users;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class UserRequestUpdateStatus extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {

      
        return [
            // @TODO
            'desireStatus' => ['required', 'boolean'],
        ];
    }

}
