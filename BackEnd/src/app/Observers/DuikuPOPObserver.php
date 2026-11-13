<?php

namespace App\Observers;

use App\Models\DuikuPOPPaymentTransaction;
use App\Models\PaymentUse;
use App\Models\ContractStatus;
use App\Models\StudentStatus;
use App\Models\Installment;
use App\Models\InstallmentStatus;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DuikuPOPObserver
{

    public function updated(DuikuPOPPaymentTransaction $payment)
    {
        // 1. Log untuk melihat SELURUH isi data $payment
        Log::info('--- DUITKU OBSERVER JALAN ---');
        Log::info('Data Payment (Current):', $payment->toArray());

        // 2. Log untuk mengecek masing-masing syarat dari IF
        Log::info('Pengecekan Syarat IF:', [
            'wasChanged_status' => $payment->wasChanged('status'), // true atau false
            'original_status'   => $payment->getOriginal('status'), // status sebelumnya apa?
            'current_status'    => $payment->status,                // status sekarang apa?
        ]);

        // 3. Blok IF Anda
        if (
            $payment->wasChanged('status') && 
            $payment->getOriginal('status') === 'PENDING' && 
            $payment->status === 'SUCCESS'
        ) {
            // Jika masuk ke sini, berarti kondisinya terpenuhi!
            Log::info('-> BERHASIL MASUK IF: Menjalankan processDuitkuPayment...');
            
            $this->processDuitkuPayment($payment);
        } else {
            // Jika masuk ke sini, berarti ada syarat yang gagal
            Log::warning('-> GAGAL MASUK IF: Salah satu syarat tidak terpenuhi.');
        }
    }

    


    public function processDuitkuPayment($duitkuTransaction)
    {
        // 1. Ambil jumlah uang bersih dari Duitku (tanpa biaya admin)
        $paymentAmount = $duitkuTransaction->base_amount; 
        
        // 2. Gunakan User ID Khusus Duitku (Misal ID ini Anda simpan di .env atau config)
        // Atau bisa juga di-query langsung: User::where('username', 'duitku_system')->first()->id;
        $duitkuSystemUserId = env('DUITKU_SYSTEM_USER_ID', 999); 

        // 3. Cari Kontrak (Ambil dari salah satu tagihan yang dibayar)
        $firstInstallment = Installment::find($duitkuTransaction->installment_ids[0]);
        $contract = $firstInstallment->contract; // Sesuaikan nama relasinya

        if ($contract) {
            // Ambil SEMUA tagihan aktif persis seperti di Kasir
            $listInstallment = $contract->installmentActiveAll; 
            
            $counter = 0;
            foreach ($listInstallment as $value) {
                // Hentikan looping jika uang dari Duitku sudah habis
                if ($paymentAmount <= 0) { 
                    break; 
                }

                $paymentUse = new PaymentUse;
                $paymentUse->payment_duitku = 1;
                $paymentUse->payment_id = $duitkuTransaction->id; // Pautkan ke transaksi Duitku
                $paymentUse->installment_id = $value->id;

                $installmentStatus = new InstallmentStatus;
                $installmentStatus->installment_id = $paymentUse->installment_id;
                
                if ($value->remainingPayment > 0) {
                    if ($paymentAmount >= $value->remainingPayment) {
                        // Lunas
                        $paymentUse->payment_use = $value->remainingPayment;
                        $paymentAmount = $paymentAmount - $value->remainingPayment;
                        $installmentStatus->status = 3;
                    } else {
                        // Bayar Sebagian
                        $paymentUse->payment_use = $paymentAmount;
                        $paymentAmount = 0;
                        $installmentStatus->status = 2;
                    }
                }
                
                $paymentUse->save();

                $installmentStatus->status_ref_id = $paymentUse->id;
                $installmentStatus->status_action = 1; // 1 = forward
                $installmentStatus->status_by_id = $duitkuSystemUserId; // <-- Menggunakan User Duitku
                $installmentStatus->save();

                // Pengecekan Kontrak Lunas (Karena ini tagihan paling terakhir di listActiveAll)
                if ($counter == count($listInstallment) - 1) {
                    if ($installmentStatus->status == 3) {
                        
                        $contractStatus = new ContractStatus;
                        $contractStatus->contract_id = $contract->id;
                        $contractStatus->status_by_id = $duitkuSystemUserId; // <-- Menggunakan User Duitku
                        $contractStatus->status_action = 4;
                        $contractStatus->status_ref_id = $duitkuTransaction->id;
                        $contractStatus->status = 5; // Kontrak Lunas
                        $contractStatus->save(); 

                        $studentStatus = new StudentStatus;
                        $studentStatus->student_id = $duitkuTransaction->student_id;
                        $studentStatus->status_ref_id = $duitkuTransaction->id;
                        $studentStatus->status_by_id = $duitkuSystemUserId; // <-- Menggunakan User Duitku
                        $studentStatus->status_action = 6;
                        $studentStatus->status = 8; // Siswa Lulus / Selesai
                        $studentStatus->save();
                    }
                }

                $counter = $counter + 1;
            }
        }
    }



}