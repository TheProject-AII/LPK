<?php

namespace App\Rules\Payment;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Contract;

class PaymentStudentInContractRule implements Rule
{
    private $contractId = null;

    public function __construct(string $contractId){
        $this->contractId = $contractId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        return Contract::where('id',$this->contractId)
                    ->where('student_id',$value)->where('status', 4)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The selected payment student is not registered in selected payment contract.';
    }
}
