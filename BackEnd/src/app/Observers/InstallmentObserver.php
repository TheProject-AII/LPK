<?php

namespace App\Observers;

use App\Models\Installment;
use App\Models\InstallmentStatus;
use App\Models\ContractStatus;

class InstallmentObserver
{

    public function created(Installment $installment)
    {
        $InstallmentStatus = new InstallmentStatus;
        $InstallmentStatus->installment_id =$installment->id;
        $InstallmentStatus->status = 1;
        $InstallmentStatus->status_by_id = auth()->user()->id;

        $InstallmentStatus->save();


    }

    public function updated(Installment $installment)
    {
        //status: 1:register  2:partial paid 3:paid 4: unpaid 5: active
        //di marking karena fungsinya redudant dengan payment observer
        // if($installment->status == 3){
        //     $lastInstallment = Installment::where('installment_contract_id',$installment->installment_contract_id)->where('status',"!=", 3)->exists();
        //     if(!$lastInstallment){
        //         $contractStatus = new ContractStatus;
        //         $contractStatus->contract_id = $installment->installment_contract_id;
        //         $contractStatus->status = 2;
        //         $contractStatus->status_by_id = auth()->user()->id; 
        //         $contractStatus->save();
        //     }
        // }


    }

    public function deleted(Installment $installment)
    {
        $InstallmentStatus = InstallmentStatus::where('installment_id',$installment->id);
        $InstallmentStatus->delete();
    }



}
