<?php

namespace App\JsonApi\Student\V1\LpkCategories;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class LpkCategoryRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {

        return [
             'lpkCategoryId' => ['required', 'int'],
             'lpkCategoryLpkId' => ['required', 'int'],
        ];
    }

}
