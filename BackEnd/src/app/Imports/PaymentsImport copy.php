<?php
namespace App\Imports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PaymentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Payment([
            'id' => $row['id'],
            'payment_no' => $row['payment_no'],
            'payment_student_id' => $row['payment_student_id'],
            'payment_contract_id' => $row['payment_contract_id'],
            'payment_method' => $row['payment_method'],
            'payment_receive_amount' => $row['payment_receive_amount'],
            'payment_change_amount' => $row['payment_change_amount'],
            'payment_remark' => $row['payment_remark'],
            'payment_cashier_id' => $row['payment_cashier_id'],
            'payment_transfer_no' => $row['payment_transfer_no'],
            'payment_at' => $row['payment_at'],
        ]);
    }
}
