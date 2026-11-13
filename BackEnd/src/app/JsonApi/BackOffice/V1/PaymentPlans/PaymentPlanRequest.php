<?php

namespace App\JsonApi\BackOffice\V1\PaymentPlans;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;

class PaymentPlanRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $data = $this->model();
        $paymentPlanLpk = isset($this->data['attributes']['paymentPlanLpkId'])?$this->data['attributes']['paymentPlanLpkId']:null;
        $paymentPlanInterest = isset($this->data['attributes']['paymentPlanInterest'])?$this->data['attributes']['paymentPlanInterest']:null;
        $paymentPlanInterestProvision = isset($this->data['attributes']['paymentPlanInterestProvision'])?$this->data['attributes']['paymentPlanInterestProvision']:null;

        // Composite unique: range + interest + interest_provision + lpk_id
        $uniquePaymentPlanRange = Rule::unique('payment_plans', 'payment_plan_range')
            ->where('payment_plan_lpk_id', $paymentPlanLpk)
            ->where('payment_plan_interest', $paymentPlanInterest);
        
        if ($paymentPlanInterestProvision !== null) {
            $uniquePaymentPlanRange->where('payment_plan_interest_provision', $paymentPlanInterestProvision);
        } else {
            $uniquePaymentPlanRange->whereNull('payment_plan_interest_provision');
        }

        if ($data) {
            $uniquePaymentPlanRange->ignoreModel($data);
        }

        return [
             'paymentPlanLpkId' => ['required', 'uuid','exists:lpks,id'],
             'paymentPlanName' => ['required', 'string'],
             'paymentPlanRange' => ['required', 'int', $uniquePaymentPlanRange],
             'paymentPlanInterest' => ['required'],
             'paymentPlanInterestProvision' => ['nullable'],
        ];
    }

}
