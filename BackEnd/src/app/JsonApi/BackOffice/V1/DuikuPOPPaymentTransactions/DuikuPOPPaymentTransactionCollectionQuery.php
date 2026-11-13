<?php

namespace App\JsonApi\BackOffice\V1\DuikuPOPPaymentTransactions;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class DuikuPOPPaymentTransactionCollectionQuery extends ResourceQuery
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
            'filter.invoiceNo' => [
                'nullable',
                'string',
            ],
            'filter.studentName' => [
                'nullable',
                'string',
            ],
            'filter.paymentMethod' => [
                'nullable',
                'string',
            ],
            'filter.status' => [
                'nullable',
                'string',
            ],
            'filter.lpkName' => [
                'nullable',
                'string',
            ],
            'filter.paidAtStart' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'filter.paidAtEnd' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'filter.updatedAtStart' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'filter.updatedAtEnd' => [
                'nullable',
                'date_format:Y-m-d',
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
