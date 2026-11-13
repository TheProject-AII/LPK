<?php

namespace App\JsonApi\BackOffice\V1\Lpks;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class LpkRequestUpdateStatus extends ResourceRequest
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
