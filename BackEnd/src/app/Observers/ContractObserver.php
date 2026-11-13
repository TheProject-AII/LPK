<?php

namespace App\Observers;

use App\Models\Contract;
use App\Models\ContractStatus;
use App\Models\StudentStatus;
use App\Models\Installment;
use App\Models\InstallmentStatus;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;


class ContractObserver
{
    //fungsi observer jika menghapus
    public function deleted(Contract $Contract)
    {

        //hapus semua data yg berafialisasi dengan id Installment
        ContractStatus::where('status_ref_id', $Contract['id'])->delete();
        StudentStatus::where('status_ref_id', $Contract['id'])->delete();


    }

    public function created(Contract $Contract)
    {

        //   status==0 REGISTERED
        //   status==1 WAITING APPROVAL
        //   status==2 REJECTED
        //   status==3 ACTIVE
        //   status==4 PAYMENT COMPLETED
        //   status==5 TERMINATED BY PAYMENT
        //   status==6 CHANGE TO OWNERSHIP PROGRAM

        // action==1 Request Approval
        // action==2 Approve
        // action==3 Reject
        // action==4 Payment Completed
        // action==5 Terminate by payment
        // action==6 CHANGE TO OWNERSHIP PROGRAM

        // status_by_id== 0 system

        // $contractStatus = new ContractStatus;
        // $contractStatus->contract_id = $Contract->id;
        // $contractStatus->status = 1;
        // $contractStatus->status_by_id = 0;
        // $contractStatus->save();





    }
 
    public function saving(Contract $Contract){

        // if(isset(request()->data["attributes"]['status'])){
        //     $status = request()->data["attributes"]['status'];
        //     Log::info($status);


        // $ContractStatusLast = ContractStatus::where('contract_id',$Contract->id)->latest('id')->first();

        // $ContractStatus = new ContractStatus;
        // $ContractStatus->contract_id = $Contract->id;
        // $ContractStatus->status_by_id = auth()->user()->id;

        // if($ContractStatusLast->status == 1 && $status == 2 ){
        //         $ContractStatus->status_action = 1;
        //         $ContractStatus->status = 2;
        //         $ContractStatus->save();
        //         // $this->notifRequest($Installment);
        //         $Contract->status = 2;

        // }
        //     elseif($ContractStatusLast->status == 2 && $status == 3 ){
        //         $ContractStatus->status_action = 2;
        //         $ContractStatus->status = 3;
        //         $ContractStatus->save();
        //         $Contract->status = 3;
        //     }
        //     elseif($ContractStatusLast->status == 2 && $status == 4 ){
        //         $ContractStatus->status_action = 3;
        //         $ContractStatus->status = 4;
        //         $ContractStatus->save();
        //         $this->generateInstallment($Contract);
        //         $Contract->status = 4;

        //     }
        //     elseif($ContractStatusLast->status == 3 && $status == 2 ){
        //         $ContractStatus->status_action = 1;
        //         $ContractStatus->status = 2;
        //         $ContractStatus->save();
        //         $Contract->status = 2;

        //     }
        // }
    }


    /**
     * Update status for a given id.
     */

    public function updateStatus(ContractSchema $schema, ContractUpdateStatusRequest $request, Contract $contract)

    {
        //status
        // 1 : registered
        // 2 : waiting approval
        // 3 : Active

        $contractStatus = new ContractStatus;
        $contractStatus->contract_id = $contract->id;
        $contractStatus->status = $request->data['attributes']['status'];
        $contractStatus->status_action = $request->data['attributes']['action'];
        $contractStatus->status_remark = $request->data['attributes']['remarks'] ?? null;
        $contractStatus->status_by_id = auth()->user()->id;
        $contractStatus->save();

        $contract->status = $request->data['attributes']['status'];


        return new DataResponse($contract);
    }


    // public function updated(Contract $Contract)
    // {
    //     // $FmsDriverStatus = new FmsDriverStatus;
    //     // $FmsDriverStatus->student_id = $Installment->student_id;
    //     // $FmsDriverStatus->status_ref_id = $Installment->id;
    //     // $FmsDriverStatus->status_by_id = 0;
    //     // $FmsDriverStatus->status_action = 5;
    //     // $FmsDriverStatus->status = 5;
    //     // $FmsDriverStatus->save();


    //     // $FmsVehicleStatus = new FmsVehicleStatus;
    //     // $FmsVehicleStatus->vehicle_id = $Installment->pko_vehicle_id;
    //     // $FmsVehicleStatus->status_ref_id = $Installment->id;
    //     // $FmsVehicleStatus->status_by_id = 0;
    //     // $FmsVehicleStatus->status_action = 5;
    //     // $FmsVehicleStatus->status = 4;
    //     // $FmsVehicleStatus->save();

    //     //Log::info($Installment->payment_plant->installment->payment_plan_installment_amount);






    // }

    // private function terminate($Installment,$return_vehicle_date, $return_status_vehicle,$return_key_vehicle,$return_stnk_vehicle){


    //     $StudentStatus = new StudentStatus;
    //     $StudentStatus->student_id = $Installment->student_id;
    //     $StudentStatus->status_ref_id = $Installment->id;
    //     $StudentStatus->status_by_id = 0;
    //     $StudentStatus->status_action = 8;
    //     $StudentStatus->status = 7;
    //     $StudentStatus->save();

    //     $checker_type = null;
    //     if($return_status_vehicle == "return"){
    //         $return_status_vehicle = 9;
    //         $checker_type = 3;
    //     }
    //     else if($return_status_vehicle == "repo"){
    //         $return_status_vehicle = 8;
    //         $checker_type = 4;
    //     }
    //     else{
    //         $return_status_vehicle = 6;
    //     }

    //     $FmsVehicleStatus = new FmsVehicleStatus;
    //     $FmsVehicleStatus->vehicle_id = $Installment->pko_vehicle_id;
    //     $FmsVehicleStatus->status_ref_id = $Installment->id;
    //     $FmsVehicleStatus->status_by_id = 0;
    //     $FmsVehicleStatus->status_action = 8;
    //     $FmsVehicleStatus->status = $return_status_vehicle;
    //     $FmsVehicleStatus->status_date = $return_vehicle_date;
    //     $FmsVehicleStatus->save();


    //     $WorkshopCheckers = new WorkshopCheckers;
    //     $WorkshopCheckers->checker_vehicle_id  = $Installment->pko_vehicle_id;
    //     $WorkshopCheckers->checker_type = $checker_type;
    //     $WorkshopCheckers->checker_kunci = $return_key_vehicle;
    //     $WorkshopCheckers->checker_stnk = $return_stnk_vehicle;
    //     $WorkshopCheckers->save();

    // }

    private function generateInstallment($Contract) {
        // Status updates
        $StudentStatus = new StudentStatus;
        $StudentStatus->student_id = $Contract->student_id;
        $StudentStatus->status_ref_id = $Contract->id;
        $StudentStatus->status_by_id = 0;
        $StudentStatus->status_action = 5;
        $StudentStatus->status = 5;
        $StudentStatus->save();

        // Calculate monthly installment
        $total_payable = $Contract->contract_total_payable;
        $payment_plan = $Contract->payment_plan; // Either 6 or 12 months

        // Calculate installment amount
        $installment_amount = $total_payable / $payment_plan;

        // Initial date values
        $start_date = $Contract->contract_start_date;
        $due_date = Carbon::parse($start_date)->addMonth(); // Add one month for due date
        $sequence_no = 1;

        // Loop to create installments for each month
        for ($month = 1; $month <= $payment_plan; $month++) {
            // Generate SPJ number
            $installment_no = 'SPJ/' . $Contract->student->student_no . '/' . Carbon::parse($due_date)->format('ymd') . '/' . $sequence_no;

            // Create a new Installment object
            $Installment = new Installment;
            $Installment->installment_no = $installment_no;
            $Installment->installment_contract_id = $Contract->id;
            $Installment->installment_sequence_no = $sequence_no;
            $Installment->installment_monthly_installment = $installment_amount;
            $Installment->installment_start_date = $start_date;
            $Installment->installment_due_date = $due_date;
            $Installment->save();

            // Update total payable
            $total_payable -= $installment_amount;

            // Increment sequence number and dates
            $sequence_no++;
            $start_date = Carbon::parse($start_date)->addMonth();
            $due_date = Carbon::parse($due_date)->addMonth();
        }
    }

   //fungsi untuk mendapat kan lastno



}
