<?php

namespace App\JsonApi\BackOffice\V1\Lpks;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

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

            'filter.lpkNo' => [
                'nullable',
                'string',
            ],
            'filter.active' => [
                'nullable',
                'integer',
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
