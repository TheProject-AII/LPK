<?php

namespace App\JsonApi\BackOffice\V1\Installments;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class InstallmentCollectionQuery extends ResourceQuery
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

            'filter.installmentNo' => [
                'nullable',
                'string',
            ],
            'filter.contract' => [
                'nullable',
                'string',
            ],
            'filter.paymentHistory' => [
                'nullable',
                'string',
            ],
            'filter.installmentSequenceNo' => [
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
