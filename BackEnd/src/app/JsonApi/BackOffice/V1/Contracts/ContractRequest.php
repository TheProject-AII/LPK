<?php

namespace App\JsonApi\BackOffice\V1\Contracts;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;
use Illuminate\Database\Query\Builder;

class ContractRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        // $uniqueVehicleProductCode = Rule::unique('vehicle_types', 'vehicle_product_code');
        $data = $this->model();
        $prohibited =  '';
        $existStudent = Rule::exists('students','id')->where(function (Builder $query) {
            return $query->where('status', 6);
        });


        if ($this->isUpdating()) {
            // $existVehicle->ignoreModel($data);
            // $existStudent->ignoreModel($data);
            $existStudent = Rule::exists('students','id');
            $prohibited =  'prohibited';
        }

        return [
            'contractStudent' => ['required', 'uuid', $existStudent],
            // 'contractStartDate' => ['required', 'date_format:Y-m-d'],
            // 'contractLpk' => [$required, 'string'],
            'contractPaymentPlan' => ['required', 'uuid'],
            'contractTotalPayable' => ['required', 'int'],
            'contractDateOfDeparture' => ['required', 'date_format:Y-m-d'],
            'contractInitialContractInJapan' => ['required', 'date_format:Y-m-d'],
            'contractEndOfContractInJapan' => ['required', 'date_format:Y-m-d'],
            'contractPayday' => ['required', 'int'],
            'contractFirstWorkingDay' => ['required', 'int', 'min:1'],
            'contractProcessFee' => ['required', 'int'],
            'contractMembership' => ['nullable', 'numeric'],
            'contractProvisionFeeEnabled' => ['nullable', 'boolean'],
            'contractTaxEnabled' => ['nullable', 'boolean'],
            'contractTaxRate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'contractPrinciple' => ['required', 'int'],
            'contractRemark' => ['nullable','string'],
            // 'contractGracePeriod' => ['required','int'],
            'contractDisbursmentDate' => ['required', 'date_format:Y-m-d'],
            'contractWageDaily' => ['required', 'int'],
            'contractCutOffDate' => ['required', 'int'],
            'contractKursYen' => ['required'],
            'contractProvisionAlreadyPaid' => ['required', 'int'],
            'contractStudentNeedTraining' => ['required', 'int'],

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
            'contractStudent.exists' => 'The selected contract student is not avaible.',

        ];
    }

}
