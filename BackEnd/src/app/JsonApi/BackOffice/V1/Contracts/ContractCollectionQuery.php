<?php

namespace App\JsonApi\BackOffice\V1\Contracts;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class ContractCollectionQuery extends ResourceQuery
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

            'filter.contractStudent' => [
                'nullable',
                'uuid',
            ],

            'filter.studentName' => [
                'nullable',
                'string',
            ],

            'filter.contractStartDate' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'filter.contractDateOfDeparture' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'filter.contractDisbursmentDate' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'filter.contractInitialContractInJapan' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'filter.contractEndOfContractInJapan' => [
                'nullable',
                'date_format:Y-m-d',
            ],


            'filter.contractLpk' => [
                'nullable',
                'uuid',
            ],

            'filter.status' => [
                'nullable',
                'integer',
            ],

            'filter.contractPayday' => [
                'nullable',
                'integer',
            ],

            'filter.contractWageDaily' => [
                'nullable',
                'integer',
            ],

            'filter.contractCutOffDate' => [
                'nullable',
                'integer',
            ],

            'filter.contractKursYen' => [
                'nullable',
                'integer',
            ],

            'filter.contractProvisionAlreadyPaid' => [
                'nullable',
                'integer',
            ],

            'filter.contractStudentNeedTraining' => [
                'nullable',
                'integer',
            ],

            'filter.contractGracePeriod' => [
                'nullable',
                'integer',
            ],

            'filter.contractNo' => [
                'nullable',
                'string',
            ],
            'filter.contractTotalPayable' => [
                'nullable',
                'integer',
            ],
            'filter.contractProcessFee' => [
                'nullable',
                'integer',
            ],
            'filter.contractPrinciple' => [
                'nullable',
                'integer',
            ],
            'filter.contractMembership' => [
                'nullable',
                'integer',
            ],
            'filter.contractProvisionFeeEnabled' => [
                'nullable',
                'boolean',
            ],
            'filter.contractTaxEnabled' => [
                'nullable',
                'boolean',
            ],
            'filter.contractTaxRate' => [
                'nullable',
                'numeric',
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
