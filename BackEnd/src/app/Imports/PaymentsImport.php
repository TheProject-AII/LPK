<?php

namespace App\Imports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PaymentsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    /**
     * Map the Excel data to the Payment model.
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Payment([
            'payment_no' => $row['payment_no'],
            'payment_student_id' => $row['payment_student_id'],
            'payment_contract_id' => $row['payment_contract_id'],
            'payment_method' => $row['payment_method'] ?? '',
            'payment_receive_amount' => $row['payment_receive_amount'],
            'payment_change_amount' => $row['payment_change_amount'] ?? 0,
            'payment_remark' => $row['payment_remark'] ?? '',
            'payment_cashier_id' => $row['payment_cashier_id'],
            'payment_transfer_no' => $row['payment_transfer_no'] ?? '',
            'payment_at' => $row['payment_at'],
        ]);
    }

    /**
     * Set batch insert size.
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 1000; // Adjust based on your requirements
    }

    /**
     * Set chunk size for processing.
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000; // Adjust based on your requirements
    }
}
