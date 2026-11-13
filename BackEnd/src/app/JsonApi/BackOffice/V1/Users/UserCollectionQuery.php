<?php

namespace App\JsonApi\BackOffice\V1\Users;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class UserCollectionQuery extends ResourceQuery
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
            'filter.userLpk' => [
                'nullable',
                'uuid',
            ],
            'filter.name' => [
                'nullable',
                'string',
            ],
            'filter.email' => [
                'nullable',
                'string',
            ],
            'filter.active' => [
                'nullable',
                'integer',
            ],
            'filter.createdAtMonthYear' => [
                'nullable',
                'date_format:Y-m',
            ],
            'filter.id' => [
                'nullable',
                'uuid',
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
