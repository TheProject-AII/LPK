<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\PaymentUse;
use App\Models\PaymentChange;
use App\Models\Contract;
use App\Models\ContractStatus;
use App\Models\StudentStatus;
use App\Models\Installment;
use App\Models\InstallmentStatus;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Request;



class PaymentObserver
{
  

    public function created(Payment $payment)
    {
        
        $this->usePayment($payment);
  
    }


    public function updated(Payment $payment)
    {
        
        if($payment->getOriginal('payment_receive_amount') > $payment->payment_receive_amount){
            $this->updateMinus($payment);
        }
        else{
            $this->updatePlus($payment);
        }
        
    }


    public function updateMinus($paymentSource){
        $different = $paymentSource->getOriginal('payment_receive_amount') - $paymentSource->payment_receive_amount ;
        
 
        // $contract = $paymentSource->paymentContract->historyPayment->sortByDesc('created_at');
        $listPaidInstallment = $paymentSource->paymentContract->installmentPaid->sortByDesc('installment_sequence_no');
        
        foreach ($listPaidInstallment as $value) {
            if($different == 0){ 
                break; 
            }
            $paymentUse = new PaymentUse;
            $paymentUse->payment_id = $paymentSource->id;
            $paymentUse->installment_id = $value->id;

            $installmentStatus = new InstallmentStatus;
            $installmentStatus->installment_id = $paymentUse->installment_id;
            
            if($value->payment_amount > 0){
                if($different == $value->payment_amount || $different > $value->payment_amount){
                    //kurangi nilai payment amount dengan nilai remaining Payment
                    $paymentUse->payment_use = -$value->payment_amount;
                    $different = $different - $value->payment_amount ;
                    $installmentStatus->status = 1;
                    
                }
                else{
                    $paymentUse->payment_use = -$different;
                    $different = 0;
                    $installmentStatus->status = 2;
                }
            }
            
            
            $paymentUse->save();

            $installmentStatus->status_ref_id = $paymentSource->paymentChange->sortByDesc('created_at')->first()->id;
            //status_action 2 = backward
            $installmentStatus->status_action = 2;
            $installmentStatus->status_by_id = auth()->user()->id;
            $installmentStatus->save();

        }
    }

    public function updatePlus($paymentSource){
        $different = $paymentSource->payment_receive_amount - $paymentSource->getOriginal('payment_receive_amount') ;

        $this->usePayment($paymentSource,$different);

    }


    public function usePayment($paymentSource,$paymentAmount=null){
        if(!$paymentAmount){
            $paymentAmount = $paymentSource->payment_receive_amount;
        }
        // $contract = Contract::findOrFail($paymentSource->payment_contract_id);
        $contract = $paymentSource->paymentContract;
        if($contract){
            $listInstallment = $contract->installmentActiveAll;
             //foreach hingga nilai payment amount 0
             $counter = 0;
            foreach ($listInstallment as $value) {
                if($paymentAmount == 0){ 
                    break; 
                }
                $paymentUse = new PaymentUse;
                $paymentUse->payment_id = $paymentSource->id;
                $paymentUse->installment_id = $value->id;

                $installmentStatus = new InstallmentStatus;
                $installmentStatus->installment_id = $paymentUse->installment_id;
                
                if($value->remainingPayment > 0){
                    if($paymentAmount == $value->remainingPayment || $paymentAmount > $value->remainingPayment){
                        //kurangi nilai payment amount dengan nilai remaining Payment
                        $paymentUse->payment_use = $value->remainingPayment;
                        $paymentAmount = $paymentAmount - $value->remainingPayment;
                        $installmentStatus->status = 3;
                        
                    }
                    else{
                        $paymentUse->payment_use = $paymentAmount;
                        $paymentAmount = 0;
                        $installmentStatus->status = 2;
                    }
                }
                
                
                $paymentUse->save();

                $installmentStatus->status_ref_id = $paymentUse->id;
                //status_action 1 = forward
                $installmentStatus->status_action = 1;
                $installmentStatus->status_by_id = auth()->user()->id;
                $installmentStatus->save();

                // Check condition if count is length -1 
                // then it is last iteration
                if( $counter == count( $listInstallment ) - 1) {
                     
                    if($installmentStatus->status == 3){
                        $contractStatus =  new ContractStatus;
                        $contractStatus->contract_id = $paymentSource->payment_contract_id;
                        $contractStatus->status_by_id = auth()->user()->id;
                        $contractStatus->status_action = 4;
                        $contractStatus->status_ref_id = $paymentSource->id;
                        $contractStatus->status = 5;
                        $contractStatus->save(); 

                        $studentStatus = new StudentStatus;
                        $studentStatus->student_id = $paymentSource->payment_student_id;
                        $studentStatus->status_ref_id = $paymentSource->id;
                        $studentStatus->status_by_id = auth()->user()->id;
                        $studentStatus->status_action = 6;
                        $studentStatus->status = 8;
                        $studentStatus->save();
                    }
                    
                }

                $counter = $counter + 1;
                    

            }
        }

    }



}
