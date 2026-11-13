<?php

namespace App\JsonApi\BackOffice\V1\Payments;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;
use Illuminate\Database\Query\Builder;
use App\Rules\Payment\PaymentReceiveAmountRule;
use App\Rules\Payment\PaymentStudentInContractRule;
use Illuminate\Support\Facades\Log;

class PaymentRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {

        //paymentMethod
        // 1 : "Cash",
        // 2 : "Bank Transfer",
        // 3 : "EDC Payment",
        // 4 : "Virtual Account",

        $data = $this->model();
        // Log::info($data->payment_receive_amount);
        // Log::info($this->data['attributes']['paymentReceiveAmount']);
        $existContract = Rule::exists('contracts','id')->where(function (Builder $query) {
            return $query->where('status', 4);
        });
        $existStudent = Rule::exists('students','id')->where(function (Builder $query) {
            return $query->where('status', 7);
        });
        return [
             'paymentContract' => ['required', 'uuid', $existContract],
             'paymentStudent' => ['required', 'uuid',$existStudent, new PaymentStudentInContractRule($this->data['attributes']['paymentContract'])],
             'paymentMethod' => ['required', 'integer'],
             'paymentReceiveAmount' => ['required', 'integer', new PaymentReceiveAmountRule($this->data['attributes']['paymentContract'],$data)],
             'paymentRemark' => ['nullable','string'],
             'paymentTransferNo' => ['required_if:paymentMethod,==,2', 'nullable','string'],
             'paymentAt' => ['required', 'date'],
            //  'paymentAta' => ['required', 'date'],
        ];
    }

}
