<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\Contract;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractInfoNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $contract;

    public function __construct($contract)
    {
        $this->contract = $contract;
    }



    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        $contract = Contract::find($this->contract->id);



        // Calculate installment amount
        $total_payable = $contract->contract_total_payable;
        $payment_plan = $contract->contractPaymentPlan->payment_plan_range;
        $payment_plan_interest = $contract->contractPaymentPlan->payment_plan_interest;
        $payment_plan_interest_provision = $contract->contractPaymentPlan->payment_plan_interest_provision ?? 0;
        $processFee = $contract->contract_process_fee / $payment_plan;
        $totalPrinciple = $contract->contract_principle / $payment_plan;
        $interestPercentage = $payment_plan_interest / 100;
        $interestProvRate = $payment_plan_interest_provision / 100;
        $totalInterestFix = $interestPercentage * ($contract->contract_principle * (($payment_plan * 30 + $contract->contract_grace_period) / ($payment_plan * 30)));

        $installment_amount = $total_payable / $payment_plan;
        $membershipPerMonth = $contract->contract_membership ?? 0;
        $provFeeEnabled = $contract->contract_provision_fee_enabled ?? false;

        $totalProvisionFeeFix = $provFeeEnabled
            ? $interestProvRate * $contract->contract_principle * (($payment_plan * 30 + $contract->contract_grace_period) / ($payment_plan * 30))
            : 0;

        $totalBeforeTaxFix = $totalPrinciple + $totalInterestFix + $membershipPerMonth + $totalProvisionFeeFix;

        if ($contract->contract_provision_already_paid == 2) {
            $totalBeforeTaxFix += $processFee;
        }

        $taxRate = $contract->contract_tax_enabled
            ? ($contract->contract_tax_rate ?? 0.5) / 100
            : 0;

        $incomeTaxFix = $totalBeforeTaxFix * $taxRate;
        $totalAfterTaxFix = $totalBeforeTaxFix + $incomeTaxFix;

        $trainingStart = Carbon::parse($contract->contract_date_of_departure);
        $trainingStart->modify('+1 day'); // Add 1 day to trainingStart

        // Calculate trainingEnd
        $trainingEnd = clone $trainingStart;
        // Log::info('trainingStart '.$trainingEnd);
        if($contract->contract_student_need_training){

            // $trainingEnd->modify('+1 month');
            //change to add 30 days instead 1 month
            $trainingEnd->modify('+30 day');
        }

        // Calculate firstMonthWorkingDateStart
        $firstMonthWorkingDateStart = clone $trainingEnd;
        $firstMonthWorkingDateStart->modify('+1 day');

        // Calculate firstMonthWorkingDateEnd
        $firstMonthWorkingDateEnd = clone $firstMonthWorkingDateStart;
        if ($firstMonthWorkingDateStart->format('d') <= $contract->contract_cut_off_date) {

            $firstMonthWorkingDateEnd->day =  $this->adjustDueDate($firstMonthWorkingDateEnd,$contract->contract_cut_off_date);

        } else {
            $firstMonthWorkingDateEnd->modify('+1 month');
            $firstMonthWorkingDateEnd->day =  $this->adjustDueDate($firstMonthWorkingDateEnd,$contract->contract_cut_off_date);;
        }

        // Calculate working days
        if(isset($contract->contract_first_working_day)){
            $workingDays = $contract->contract_first_working_day;
        }
        else{
            $workingDays =   $this->calculateWorkingDays($firstMonthWorkingDateStart.getTimestamp(),$firstMonthWorkingDateEnd.getTimestamp(),$contract->contract_days_off);
        }


        $kursYen = (float)$contract->contract_kurs_yen;
        $firstSalary = $workingDays * $contract->contract_wage_daily * $kursYen;
        $remainingFirstSalary = $firstSalary - $installment_amount;


        $startDateFix = clone $trainingEnd;
        $firstCutOffDate = clone $trainingEnd;
        $firstPaymentDate = clone $trainingEnd;



        // Log::info('firstCutOffDate 1 '.$firstCutOffDate);
        $firstPaymentDate->day = min($contract->contract_payday, $firstCutOffDate->daysInMonth);
        $firstCutOffDate->day = min($contract->contract_cut_off_date, $firstCutOffDate->daysInMonth);
        // Log::info('firstCutOffDate 2 '.$firstCutOffDate);
        // Calculate based on the remaining first salary
        if ($remainingFirstSalary < 5000000 && !($firstPaymentDate < $firstCutOffDate)) {
            $startDateFix->addMonth();
        } else if ($firstPaymentDate < $firstCutOffDate && $remainingFirstSalary < 5000000) {
            $startDateFix->addMonths(2);
        }
        else if ($firstPaymentDate < $firstCutOffDate && $remainingFirstSalary > 5000000) {
            $startDateFix->addMonths(1);
        }
        else if($firstPaymentDate < $firstMonthWorkingDateEnd){
            $startDateFix->addMonths(1);
        }


          $startDateFix->day =  $this->adjustDueDate($startDateFix,$contract->contract_payday);


        // $startDateFix->day = $contract->contract_payday;

        $startDateReallyFix = clone $startDateFix;


        $start_date = $startDateReallyFix->copy();
        $due_date = $startDateReallyFix->copy()->addMonthNoOverflow();
        $sequence_no = 1;
        $installments = [];

        // Installment loop
        for ($month = 1; $month <= $payment_plan; $month++) {
            $installment_no = 'Installment/' . $contract->contractStudent->student_no . '/' . $due_date->format('ym') . '/' . $sequence_no;
            $startDateAdjusted = $start_date->copy();
            $startDateAdjusted->day = $this->adjustDueDate($startDateAdjusted, $contract->contract_payday);
            $dueDateAdjusted = $due_date->copy();
            $dueDateAdjusted->day = $this->adjustDueDate($dueDateAdjusted, $contract->contract_payday);

            $installments[] = [
                'start_date' => $startDateAdjusted->format('Y-m-d'),
                'due_date' => $dueDateAdjusted->format('Y-m-d'),
                'interest' => number_format($totalInterestFix),
                'provision_fee' => number_format($totalProvisionFeeFix),
                'process_fee' => number_format($processFee),
                'principle' => number_format($totalPrinciple),
                'membership' => number_format($membershipPerMonth),
                'total_before_tax' => number_format($totalBeforeTaxFix),
                'total_income_tax' => number_format($incomeTaxFix),
                'total_after_tax' => number_format($installment_amount),
            ];

            if ($month == 1) {
                $contract->contract_start_date = $startDateReallyFix->toDateString();
            }
            if ($month == $payment_plan) {
                $contract->contract_end_date = $dueDateAdjusted->toDateString();
            } else {
                $sequence_no++;
                $start_date = $start_date->copy()->addMonthNoOverflow();
                $due_date = $due_date->copy()->addMonthNoOverflow();
            }
        }
        return (new MailMessage)
                        ->line('Contract with this data')
                        ->line('Contract No:  ' . str_pad($this->contract->contract_no, 30))  // Adjust the padding as needed
                        ->line('Details:')
                        ->view('emails.contract_info', [
                            'contractNo' => $this->contract->contract_no,
                            'student_name' => $this->contract->contractStudent->student_name,
                            'lpk_name' => $this->contract->contractLpk->lpk_name,
                            'installments' => $installments,
                            'training_start' => $trainingStart->format('Y-m-d'),
                            'training_end' => $trainingEnd->format('Y-m-d'),
                            'first_date_start_working' => $firstMonthWorkingDateStart->format('Y-m-d'),
                            'first_date_end_working' => $firstMonthWorkingDateEnd->format('Y-m-d'),
                            'working_day' => $workingDays,
                            'first_salary' => number_format($firstSalary),
                            'grace_period' => $this->contract->contract_grace_period,


                        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    private function fetchJapaneseHolidays() {
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
    private function calculateWorkingDays($startDate, $endDate) {
        if ($startDate && $endDate) {
            $start = Carbon::createFromTimestamp($startDate / 1000);
            $end = Carbon::createFromTimestamp($endDate / 1000);
            $workingDays = 0;

            $period = CarbonPeriod::create($start, $end);

            foreach ($period as $date) {
                // Check if the day is not Saturday (6) or Sunday (7) and not a Japanese holiday
                if ($date->format('N') < 6 && !$this->isJapaneseHoliday($date)) {
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
}
