<?php

namespace App\JsonApi\Public\v1\Lpks;

use LaravelJsonApi\Validation\Rule as JsonApiRule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;

class LpkCollectionQuery extends ResourceQuery
{

    /**
     * Get the validation rules that apply to the request query parameters.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'fields' => [
                'nullable',
                'array',
                JsonApiRule::fieldSets(),
            ],
            'filter' => [
                'nullable',
                'array',
                JsonApiRule::filter(),
            ],
            'filter.createdAtMonthYear' => [
                'nullable',
                'date_format:Y-m',
            ],

            'filter.id' => [
                'nullable',
                'uuid',
            ],

            'filter.lpkName' => [
                'nullable',
                'string',
            ],

            'filter.lpkBusinessType' => [
                'nullable',
                'string',
            ],

            'filter.lpkPhoneNo' => [
                'nullable',
                'string',
            ],

            'filter.lpkEmail' => [
                'nullable',
                'string',
            ],

            'filter.lpkAddress' => [
                'nullable',
                'string',
            ],


            'filter.lpkContactPersonIdentityNo' => [
                'nullable',
                'string',
            ],

            'filter.lpkContactPersonName' => [
                'nullable',
                'string',
            ],

            'include' => [
                'nullable',
                'string',
                JsonApiRule::includePaths(),
            ],
            'page' => [
                'nullable',
                'array',
                JsonApiRule::page(),
            ],
            'sort' => [
                'nullable',
                'string',
                JsonApiRule::sort(),
            ],
            'withCount' => [
                'nullable',
                'string',
                JsonApiRule::countable(),
            ],
        ];
    }
}
