<?php

namespace App\JsonApi\BackOffice\V1\Students;

use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class StudentCollectionQuery extends ResourceQuery
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

            // 'filter.studentType' => [
            //     'nullable',
            //     'integer',
            // ],

            'filter.studentGender' => [
                'nullable',
                'integer',
            ],

            'filter.studentReligion' => [
                'nullable',
                'integer',
            ],

            'filter.studentBloodType' => [
                'nullable',
                'integer',
            ],

            'filter.studentLastEducation' => [
                'nullable',
                'integer',
            ],

            'filter.status' => [
                'nullable',
                'integer',
            ],

            'filter.studentLpk' => [
                'nullable',
                'uuid',
            ],

            'filter.studentPlaceOfBirth' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'filter.studentName' => [
                'nullable',
                'string',
            ],

            'filter.studentType' => [
                'nullable',
                'string',
            ],

            'filter.studentNo' => [
                'nullable',
                'string',
            ],

            'filter.studentAddress' => [
                'nullable',
                'string',
            ],

            'filter.studentEmail' => [
                'nullable',
                'string',
            ],

            'filter.studentPhoneNo' => [
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
