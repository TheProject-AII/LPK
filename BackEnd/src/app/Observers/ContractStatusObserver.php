<?php

namespace App\Observers;

use App\Models\Contract;
use App\Models\ContractStatus;
use App\Models\StudentStatus;
use App\Models\Installment;
use App\Models\InstallmentStatus;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;


class ContractStatusObserver
{


    public function saving(ContractStatus $contractStatus){

        //   status==1 Register
        //   status==2 Waiting Aproval
        //   status==3 Active
        //   status==4 Compleate

        // Log::info($contractStatus);

        if($contractStatus->status == 4){
             $this->generateInstallment($contractStatus->contract_id);

         }
            // else if($contractStatus->status == 2 && $contractStatus->status_action == 3 ){
            //     // $ContractStatus->status_action = 3;
            //     // $ContractStatus->status = 3;
            //     // $ContractStatus->save();


            // }
            // else if($contractStatus->status == 2 && $contractStatus->status_action == 4 ){
            //     // $ContractStatus->status_action = 2;
            //     // $ContractStatus->status = 4;
            //     // $ContractStatus->save();
            //     //generate Installment
            //     $this->generateInstallment($contractStatus->contract_id);

            // }
            // else if($contractStatus->status == 4 && $contractStatus->status_action == 5 ){
            //     // $ContractStatus->status_action = 4;
            //     // $ContractStatus->status = 5;
            //     // $ContractStatus->save();
            // }


            // else if($contractStatus->status == 3 && $contractStatus->status_action == 2 ){
            //     // $ContractStatus->status_action = 1;
            //     // $ContractStatus->status = 2;
            //     // $ContractStatus->save();

            // }

    }





    // private function generateInstallment($contract_id) {
    //     $contract = Contract::find($contract_id);

    //     // Status updates
    //     $StudentStatus = new StudentStatus;
    //     $StudentStatus->student_id =$contract->student_id;
    //     $StudentStatus->status_ref_id =$contract->id;
    //     $StudentStatus->status_by_id = Auth::id();
    //     $StudentStatus->status_action = 8;
    //     $StudentStatus->status = 7;
    //     $StudentStatus->save();

    //     // Calculate monthly installment
    //     $total_payable = $contract->contract_total_payable;
    //     $payment_plan = $contract->contractPaymentPlan->payment_plan_range - $contract->contract_grace_period; // Either 6 or 12 months

    //     // Calculate installment amount
    //     $installment_amount = $total_payable / $payment_plan;

    //     // Initial date values
    //     $contract_start_date = Carbon::parse($contract->contract_start_date);
    //     $start_date = $contract_start_date->addMonths($contract->contract_grace_period);

    //     // Combine with contract_payday value
    //     $contract_payday = $contract->contract_payday; // Assuming this value is between 1 and 31
    //     $start_date->day = $contract_payday;

    //     // Due date is one month after the start date
    //     $due_date = $start_date->copy()->addMonth();
    //     $sequence_no = 1;

    //     // Loop to create installments for each month
    //     for ($month = 1; $month <= $payment_plan; $month++) {
    //         // Generate Installment number
    //         $installment_no = 'Installment/' . $contract->contractStudent->student_no . '/' . $due_date->format('ym') . '/' . $sequence_no;

    //         // Create a new Installment object
    //         $Installment = new Installment;
    //         $Installment->installment_no = $installment_no;
    //         $Installment->installment_contract_id = $contract->id;
    //         $Installment->installment_sequence_no = $sequence_no;
    //         $Installment->installment_monthly_installment = $installment_amount;
    //         $Installment->installment_start_date = $start_date->toDateString();
    //         $Installment->installment_due_date = $due_date->toDateString();
    //         $Installment->save();

    //         // Update total payable
    //         $total_payable -= $installment_amount;

    //         // Increment sequence number and dates
    //         $sequence_no++;
    //         $start_date = $start_date->copy()->addMonth();
    //         $due_date = $due_date->copy()->addMonth();
    //     }

    //     // Update contract end date
    //     $contract->contract_end_date = $due_date->toDateString();
    //     $contract->save();
    // }


function fetchJapaneseHolidays() {
    $currentYear = date('Y');
    $nextYear = $currentYear + 1;
    $holidays = [];

    try {
        // Fetch holidays for the current year
        $currentYearUrl = "https://date.nager.at/api/v3/publicholidays/$currentYear/JP";
        $currentYearResponse = file_get_contents($currentYearUrl);
        $currentYearHolidays = json_decode($currentYearResponse, true);

        // Fetch holidays for the next year
        $nextYearUrl = "https://date.nager.at/api/v3/publicholidays/$nextYear/JP";
        $nextYearResponse = file_get_contents($nextYearUrl);
        $nextYearHolidays = json_decode($nextYearResponse, true);

        // Combine and extract holiday dates
        foreach ($currentYearHolidays as $holiday) {
            $holidays[] = $holiday['date'];
        }
        foreach ($nextYearHolidays as $holiday) {
            $holidays[] = $holiday['date'];
        }
    } catch (Exception $e) {
        error_log("Error fetching Japanese holidays: " . $e->getMessage());
    }

    return $holidays;
}

// Function to check if a date is a Japanese holiday
private function isJapaneseHoliday($date) {
    // Assume $date is a Carbon object
    $isoDate = $date->format('Y-m-d');

    // Fetch Japanese holidays
    $japaneseHolidays = $this->fetchJapaneseHolidays();

    // Check if the $isoDate is in the array of Japanese holidays
    return in_array($isoDate, $japaneseHolidays);
}

// Function to calculate working days between two dates
// private function calculateWorkingDays($startDate, $endDate, $contractDaysOff) {
//     if ($startDate && $endDate) {
//         $start = Carbon::createFromTimestamp($startDate / 1000);
//         $end = Carbon::createFromTimestamp($endDate / 1000);
//         $workingDays = 0;

//         $period = CarbonPeriod::create($start, $end);

//         foreach ($period as $date) {
//             // Check if the day is not Saturday (6) or Sunday (7) and not a Japanese holiday
//             if($contractDaysOff){
//                 if (!in_array($date->format('N'), $contractDaysOff) && !$this->isJapaneseHoliday($date)) {
//                     $workingDays++;
//                 }
//             }
//             else{
//                 if ($date->format('N') < 6 && !$this->isJapaneseHoliday($date)) {
//                     $workingDays++;
//                 }
//             }

//         }

//         return $workingDays;
//     } else {
//         return 0;
//     }
// }
private function calculateWorkingDays($startDate, $endDate, $contractDaysOff = null) {
    if ($startDate && $endDate) {
        $start = Carbon::createFromTimestamp($startDate / 1000);
        $end = Carbon::createFromTimestamp($endDate / 1000);
        $workingDays = 0;

        // If $contractDaysOff is null, default to excluding Saturday (6) and Sunday (7)
        $contractDaysOff = $contractDaysOff ?? [6, 7];

        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $dayOfWeek = $date->format('N'); // 1 = Monday, ..., 7 = Sunday

            // Check if the day is NOT in contractDaysOff and NOT a Japanese holiday
            if (!in_array($dayOfWeek, $contractDaysOff) && !$this->isJapaneseHoliday($date)) {
                $workingDays++;
            }
        }

        return $workingDays;
    } else {
        return 0;
    }
}




private function adjustDueDate($startDateFix, $contractPayday) {
    // Create a Carbon instance from the start date
    $date = clone $startDateFix;

    // Determine the last day of the next month
    // $nextMonth = $date->copy()->addMonth()->startOfMonth();
    $daysInMonth = $date->daysInMonth;

    // If the contractPayday exceeds the days in the month, set the dueDate to the last day of that month
    if ($contractPayday > $daysInMonth) {
        return $daysInMonth;

    } else {
        return $contractPayday;
    }
}










    private function generateInstallment($contract_id) {
        $contract = Contract::find($contract_id);
        // Status updates
        $StudentStatus = new StudentStatus;
        $StudentStatus->student_id =$contract->student_id;
        $StudentStatus->status_ref_id =$contract->id;
        $StudentStatus->status_by_id = Auth::id();
        $StudentStatus->status_action = 8;
        $StudentStatus->status = 7;
        $StudentStatus->save();

        // Calculate monthly installment
        $total_payable = $contract->contract_total_payable;
        $payment_plan = $contract->contractPaymentPlan->payment_plan_range; // Either 6 or 12 months

        // Calculate installment amount
        $installment_amount = $total_payable / $payment_plan;

        // Initial date values


        $trainingStart = Carbon::parse($contract->contract_date_of_departure);
        $trainingStart->modify('+1 day'); // Add 1 day to trainingStart

        
        // Calculate trainingEnd
        $trainingEnd = clone $trainingStart;
        // log::info($trainingEnd);
        
        if($contract->contract_student_need_training == 1){

            $trainingEnd->modify('+30 days');
        } else {

        $trainingEnd->modify('-1 day');
        }

        // log::info($trainingEnd);


        // Calculate firstMonthWorkingDateStart
        $firstMonthWorkingDateStart = clone $trainingEnd;
        $firstMonthWorkingDateStart->modify('+1 day');

        // Calculate firstMonthWorkingDateEnd
        $firstMonthWorkingDateEnd = clone $firstMonthWorkingDateStart;
        if ($firstMonthWorkingDateStart->format('d') <= $contract->contract_cut_off_date) {
            $firstMonthWorkingDateEnd->day =  $this->adjustDueDate($firstMonthWorkingDateEnd,$contract->contract_cut_off_date);
        } else {
            $firstMonthWorkingDateEnd->modify('+1 month');
            $firstMonthWorkingDateEnd->day =  $this->adjustDueDate($firstMonthWorkingDateEnd,$contract->contract_cut_off_date);
        }

        // Calculate working days
        if(isset($contract->contract_first_working_day)){
            $workingDays = $contract->contract_first_working_day;
        }
        else{
            // $timeDifference = $firstMonthWorkingDateEnd->getTimestamp() - $firstMonthWorkingDateStart->getTimestamp();
            // $workingDays = ceil($timeDifference / (60 * 60 * 24)) - 1; // Convert to days and subtract 1
            $workingDays =   $this->calculateWorkingDays($firstMonthWorkingDateStart.getTimestamp(),$firstMonthWorkingDateEnd.getTimestamp());
        }


        $kursYen = (float)$contract->contract_kurs_yen;
        $firstSalary = $workingDays * $contract->contract_wage_daily * $kursYen;
        $remainingFirstSalary = $firstSalary - $installment_amount;


        // Calculate based on the remaining first salary
        // if ($remainingFirstSalary > 5000000) {
        //     $startDateFix = clone $trainingEnd;
        //     $startDateFix->modify('+1 month'); // Move to the next month
        // } else {
        //     $startDateFix = clone $trainingEnd;
        //     $startDateFix->modify('+2 months'); // Move to two months later
        // }
        //     $startDateFix = clone $trainingEnd;
        // if ($remainingFirstSalary > 5000000) {
        //     $startDateFix->modify('+1 month'); // Move to the next month
        // }

        // if ($startDateFix->day > $contract->contract_cut_off_date){
        //     $startDateFix->modify('+1 month'); // Move to the next month
        //   }

        $startDateFix = clone $trainingEnd;
        $firstCutOffDate = clone $trainingEnd;
        $firstPaymentDate = clone $trainingEnd;

        // Log::info('daysInMonth1 '.$firstCutOffDate->daysInMonth);
        // Log::info('contractCutOffDate '.$contract->contract_cut_off_date);
        // Log::info('firstCutOffDate -1 '.min($contract->contract_cut_off_date, $firstCutOffDate->daysInMonth));


        // Log::info('firstCutOffDate 1 '.$firstCutOffDate);
        $firstPaymentDate->day = min($contract->contract_payday, $firstCutOffDate->daysInMonth);
        $firstCutOffDate->day = min($contract->contract_cut_off_date, $firstCutOffDate->daysInMonth);
        // Log::info('firstCutOffDate 2 '.$firstCutOffDate);
        // log::info($startDateFix);

        // Log::info('sebelum', ['startDateFix' => $startDateFix->toDateTimeString()]);
        // Calculate based on the remaining first salary
        if ($remainingFirstSalary < 5000000 && !($firstPaymentDate < $firstCutOffDate)) {
            $startDateFix->addMonth(1);
        } else if ($firstPaymentDate < $firstCutOffDate && $remainingFirstSalary < 5000000) {
            $startDateFix->addMonths(2);
        }
        else if ($firstPaymentDate < $firstCutOffDate && $remainingFirstSalary > 5000000) {
            $startDateFix->addMonths(1);
        }
        else if($firstPaymentDate < $firstMonthWorkingDateEnd){
            $startDateFix->addMonths(1);
        }

        // log::info($startDateFix);

        // Log::info('sesudah', ['startDateFix' => $startDateFix->toDateTimeString()]);




          $startDateFix->day =  $this->adjustDueDate($startDateFix,$contract->contract_payday);


        // $startDateFix->day = $contract->contract_payday;

        $startDateReallyFix = clone $startDateFix;
        // $startDateReallyFix->modify('-1 month');


        // if($contract->contract_student_need_training == 0){
        //     $startDateReallyFix->modify('-1 month');
        // }



        // // Due date is one month after the start date
        // $due_date = $startDateReallyFix->copy()->addMonth();
        // $sequence_no = 1;
        // // Loop to create installments for each month
        // for ($month = 1; $month <= $payment_plan; $month++) {
        //     // Generate Installment number
        //     $installment_no = 'Installment/' . $contract->contractStudent->student_no . '/' . $due_date->format('ym') . '/' . $sequence_no;

        //     // Create a new Installment object
        //     $Installment = new Installment;
        //     $Installment->installment_no = $installment_no;
        //     $Installment->installment_contract_id = $contract->id;
        //     $Installment->installment_sequence_no = $sequence_no;
        //     $Installment->installment_monthly_installment = $installment_amount;
        //     $Installment->installment_start_date = $startDateReallyFix->toDateString();
        //     $Installment->installment_due_date = $due_date->toDateString();
        //     $Installment->save();

        //     // Update total payable
        //     $total_payable -= $installment_amount;


        //     // Check if this is the first installment
        //     if ($month == 1) {
        //         // Add any special handling for the first installment here
        //         $contract->contract_start_date = $startDateReallyFix->toDateString();
        //     }
        //     // Check if this is the last installment
        //     if ($month == $payment_plan) {
        //         // Add any special handling for the last installment here
        //         $contract->contract_end_date = $due_date->toDateString();
        //     }
        //     else{
        //         // Increment sequence number and dates
        //         $sequence_no++;
        //         $startDateReallyFix = $startDateReallyFix->copy()->addMonth();
        //         $due_date = $due_date->copy()->addMonth();
        //     }
        // }


        // Due date is one month after the start date
        // $due_date = $startDateReallyFix->copy()->addMonth();


        $start_date = $startDateReallyFix->copy();
        $due_date = $startDateReallyFix->copy()->addMonthNoOverflow();
        $sequence_no = 1;
        // Loop to create installments for each month
        for ($month = 1; $month <= $payment_plan; $month++) {
            // Generate Installment number
            $installment_no = 'Installment/' . $contract->contractStudent->student_no . '/' . $due_date->format('ym') . '/' . $sequence_no;

            $startDateAdjusted = $start_date->copy();
            $startDateAdjusted->day = $this->adjustDueDate($startDateAdjusted, $contract->contract_payday);
            $dueDateAdjusted = $due_date->copy();
            $dueDateAdjusted->day = $this->adjustDueDate($dueDateAdjusted, $contract->contract_payday);
            // Create a new Installment object
            $Installment = new Installment;
            $Installment->installment_no = $installment_no;
            $Installment->installment_contract_id = $contract->id;
            $Installment->installment_sequence_no = $sequence_no;
            $Installment->installment_monthly_installment = round($installment_amount);
            $Installment->installment_start_date = $startDateAdjusted->toDateString();
            $Installment->installment_due_date = $dueDateAdjusted->toDateString();
            $Installment->save();

            // Update total payable
            $total_payable -= $installment_amount;


            // Check if this is the first installment
            if ($month == 1) {
                // Add any special handling for the first installment here
                $contract->contract_start_date = $startDateReallyFix->toDateString();
            }
            // Check if this is the last installment
            if ($month == $payment_plan) {
                // Add any special handling for the last installment here
                $contract->contract_end_date = $dueDateAdjusted->toDateString();
            }
            else{
                // Increment sequence number and dates
                $sequence_no++;
                $start_date = $start_date->copy()->addMonthNoOverflow();
                $due_date = $due_date->copy()->addMonthNoOverflow();
            }
        }

        // Update contract end date

        $contract->save();
    }


   //fungsi untuk mendapat kan lastno



}
