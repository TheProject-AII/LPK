<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Installment;
use App\Models\Contract;
use App\Models\InstallmentAdditionalFee;

class CommandCheckInstalmentDueDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-instalment-due-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Instalment Due Date and if student late payment will add pinalty 5% from installment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      // $contract = Contract::find('9d383051-7467-47f5-be47-d2fb0a96b67f');
      // Log::info($contract);

      // return;
      //cari instalment yang contract yang masih aktiv dan tanggal due date yang sudah terlewat dari hari ini  
      $installments = Installment::whereHas('contract', function ($query) {
            $query->where('status', 4);
        })
        ->where('status', '!=', 3)
        ->whereRaw('DATE_ADD(installment_due_date, INTERVAL 3 DAY) < ?', [Carbon::now()])
        ->get();
        foreach ($installments as $installment) {
          if($installment->additionalFee->count() > 0){
            // if(Carbon::parse($installments->installment_due_date)->addDays(3) < Carbon::now()){
              if(Carbon::parse($installment->installment_due_date) < Carbon::now()){
                $installmentDueDate = Carbon::parse($installment->installment_due_date);
                $now = Carbon::now();
    
                $monthsDifference = $installmentDueDate->diffInMonths($now);
    
                if ($installmentDueDate->isSameMonth($now)) {
                    $monthsDifference = 1;
                } else {
                    // Adjust if the day portion of the dates impacts the comparison.
                    if ($installmentDueDate->isFuture()) {
                        $monthsDifference++;
                    }
                }

                $remaningPayment = $installment->installment_monthly_installment;
                if($installment->payment_amount > 0){
                  $remaningPayment = $installment->installment_monthly_installment - $installment->payment_amount;
                }

                $InstallmentAdditionalFee = InstallmentAdditionalFee::find($installment->additionalFee[0]['id']);
                $InstallmentAdditionalFee->installment_additional_fee = $remaningPayment * 0.05 * $monthsDifference;
                $InstallmentAdditionalFee->installment_additional_fee_remark = 'Late payment '.$monthsDifference.' month from due date '.$installment->installment_due_date.' from remaning payment '.$remaningPayment;
                $InstallmentAdditionalFee->save();
            }

          }
          else{
            if(Carbon::parse($installment->installment_due_date)->addDays(3) < Carbon::now()){
                $installmentDueDate = Carbon::parse($installment->installment_due_date);
                $now = Carbon::now();

                $monthsDifference = $installmentDueDate->diffInMonths($now);

                if ($installmentDueDate->isSameMonth($now)) {
                    $monthsDifference = 1;
                } else {
                    // Adjust if the day portion of the dates impacts the comparison.
                    if ($installmentDueDate->isFuture()) {
                        $monthsDifference++;
                    }
                }
                

                $remaningPayment = $installment->installment_monthly_installment;
                if($installment->payment_amount > 0){
                  $remaningPayment = $installment->installment_monthly_installment - $installment->payment_amount;
                }

                $InstallmentAdditionalFee = new InstallmentAdditionalFee();
                $InstallmentAdditionalFee->installment_id = $installment->id;
                $InstallmentAdditionalFee->installment_additional_fee = $installment->installment_monthly_installment * 0.05 * $monthsDifference;
                $InstallmentAdditionalFee->installment_additional_fee_type = 1;
                $InstallmentAdditionalFee->installment_additional_fee_remark = 'Late payment '.$monthsDifference.' month from due date '.$installment->installment_due_date.' from remaning payment '.$remaningPayment;
                $InstallmentAdditionalFee->save();
              }
            
          }
        }
      
      
          
    }


     


     

}

