<?php

namespace App\JsonApi\BackOffice\V1\Students;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;
use Illuminate\Support\Facades\Log;

class StudentUpdateStatusRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'integer'],
            'action' => ['required', 'integer'],
            'remarks' => ['string'],
        ];
    }

}
