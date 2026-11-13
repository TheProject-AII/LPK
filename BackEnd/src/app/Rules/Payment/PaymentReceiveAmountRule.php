<?php

namespace App\Rules\Payment;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Contract;
use App\Models\Payment;
use Illuminate\Support\Facades\Log; 

class PaymentReceiveAmountRule implements Rule
{
    private $remainingPayment = null;
    private $currentPayment = null;

    public function __construct(string $requestCondtractId,Payment $current = null){
        $contract = Contract::where('id',$requestCondtractId)->first();
        $this->remainingPayment = $contract?$contract->contractRemainingPayment:null;
        $this->currentPayment = $current?$current:null;
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
        if($this->currentPayment){
           
                if($value > $this->currentPayment->payment_receive_amount + $this->remainingPayment){
                    return false;
                 }
            // Log::info('new '.$value);
            // Log::info('current '.$this->currentPayment->payment_receive_amount);
            // Log::info('emaining '.$this->remainingPayment);
            // Log::info('current + remaning '.$this->currentPayment->payment_receive_amount + $this->remainingPayment);
           
        }
        else if($this->remainingPayment == null || $this->remainingPayment < $value){
            return false;
        }
        
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        
        
        if($this->remainingPayment == null){
            return 'The payment receive amount is greater than the remaining payment.';
        }
        else{
            return 'The payment receive amount is greater than the remaining payment ' . $this->remainingPayment .'.';
        }
    }
}