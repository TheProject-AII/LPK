<?php

namespace App\JsonApi\BackOffice\V1\Installments;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;
use Illuminate\Database\Query\Builder;

class InstallmentRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {

        $data = $this->model();
        $uniqueInstallmentNo = Rule::unique('Installments', 'installment_no');

        if ($data) {
            $uniqueInstallmentNo->ignoreModel($data);
        }

        return [
            'installment_no' => ['required', 'string'],
            // 'contract' => ['required', 'uuid'],
            // 'contractStartDate' => ['required', 'date_format:Y-m-d', 'before:contractEndDate'],
            // 'contractLpk' => ['required', 'string'],
            // 'contractPaymentPlan' => ['required', 'string'],
            // 'contractTotalPayable' => ['required', 'string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [


        ];
    }

}
