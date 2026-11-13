<?php

namespace App\Http\Controllers\Api\Duitku\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Installment;
use App\Models\DuikuPOPPaymentTransaction;
use Illuminate\Support\Facades\Validator;

class DuitlkuPOPController extends Controller
{
    public function createPayment(Request $request)
    {
        // 1. Validasi Input Dasar
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:10000', // Minimal 10rb (aturan Duitku)
            'installments' => 'required|array|min:1', // Harus array dan minimal ada 1 item
            'installments.*' => 'uuid', // SETIAP item di dalam array harus format UUID
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422); 
        }
        
        $amount = (int) $request->input('amount');
        $installments = $request->input('installments');
        $studentId = auth()->id();

        // 2. CEK TRANSAKSI PENDING
        $pendingPayment = DuikuPOPPaymentTransaction::where('student_id', $studentId)
            ->where('status', 'PENDING')
            ->first();

        if ($pendingPayment) {
            // Cek apakah transaksi ini sudah melewati batas waktu 60 menit (sesuai expiryPeriod)
            // Kita beri toleransi tambahan 5 menit (total 65 menit) untuk jaga-jaga delay server
            $expiryTime = $pendingPayment->created_at->addMinutes(65);

            if (now()->greaterThan($expiryTime)) {
                // Jika sudah LEWAT dari batas waktu, otomatis ubah jadi EXPIRED di database kita
                $pendingPayment->update(['status' => 'EXPIRED']);
                
                // Karena sudah di-set EXPIRED, program BOLEH LANJUT memproses pembuatan tagihan baru di bawahnya.
                Log::info("Tagihan PENDING sebelumnya (ID: {$pendingPayment->id}) otomatis di-Expired-kan karena lewat waktu.");
            } else {
                // Jika MASIH DALAM WAKTU 60 menit, blokir user dan suruh lanjut bayar yang ini
                return response()->json([
                    'status' => 'pending_exists',
                    'message' => 'Anda masih memiliki transaksi yang belum dibayar. Silakan bayar atau batalkan terlebih dahulu.',
                    'existing_reference' => $pendingPayment->duitku_reference, 
                    'order_id' => $pendingPayment->merchant_order_id
                ], 400);
            }
        }

        $merchantCode = env('DUITKU_MERCHANT_CODE');
        $merchantKey = env('DUITKU_MERCHANT_KEY');
        
        // Ambil data kontrak aktif user
        $dataStudentContractActive = auth()->user()->studentContractActive;

        if (!$dataStudentContractActive) {
            return response()->json(['message' => 'Kontrak aktif tidak ditemukan.'], 404);
        }

        // 3. Cek Validitas Installment di DB
        $dbInstallments = Installment::whereIn('id', $installments)
            ->where('status', '!=', 3)
            ->where('installment_contract_id', $dataStudentContractActive->id)
            ->get();

        if ($dbInstallments->count() !== count($installments)) {
            return response()->json(['message' => 'Beberapa tagihan tidak valid atau sudah dibayar.'], 400);
        }

        // 4. Validasi Total Nominal (Proteksi Manipulasi Harga)
        $totalDbAmount = (int) $dbInstallments->sum('remainingPayment'); 
        
        if ($amount !== $totalDbAmount) {
            // Saya aktifkan blokir ini agar user iseng dari Flutter tidak bisa ganti harga
            Log::warning("Manipulasi nominal terdeteksi. User: " . $studentId);
            return response()->json(['message' => 'Nominal pembayaran tidak sesuai dengan tagihan di sistem.'], 400);
        }

        // 5. BUAT TRANSAKSI DI DATABASE LOKAL (Status PENDING)
        $merchantOrderId = 'INV-' . time();
        
        $transaction = DuikuPOPPaymentTransaction::create([
            'student_id' => $studentId,
            'merchant_order_id' => $merchantOrderId,
            'base_amount' => $amount,
            'total_amount' => $amount, // Akan di-update saat callback (jika ada fee)
            'admin_fee' => 0,          // Akan di-update saat callback
            'installment_ids' => $installments,
            'status' => 'PENDING'
        ]);

        // --- 6. PROSES DUITKU POP ---
        $timestamp = round(microtime(true) * 1000); 
        $signature = hash('sha256', $merchantCode . $timestamp . $merchantKey);

        $payload = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $amount,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => 'Pembayaran ' . count($installments) . ' Tagihan',
            'additionalParam' => implode(',', $installments),
            'email' => auth()->user()->student_email ?? 'siswa@example.com', 
            'callbackUrl' => env('DUITKU_CALLBACK_URL'),
            'returnUrl' => env('DUITKU_RETURN_URL'),
            'expiryPeriod' => 60
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-duitku-signature' => $signature,
                'x-duitku-timestamp' => $timestamp,
                'x-duitku-merchantcode' => $merchantCode,
            ])->post(env('DUITKU_ENV') === 'production' ? 'https://api-prod.duitku.com/api/merchant/createInvoice' : 'https://api-sandbox.duitku.com/api/merchant/createInvoice', $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['reference'])) {
                
                // UPDATE REFERENCE DARI DUITKU KE DATABASE KITA
                $transaction->update([
                    'duitku_reference' => $result['reference']
                ]);

                return response()->json([
                    'status' => 'success',
                    'reference' => $result['reference'],
                ], 200);

            } else {
                // Jika Duitku gagal memproses, batalkan transaksi yang barusan dibuat
                $transaction->update(['status' => 'FAILED']);
                
                Log::error('Duitku POP Error: ', (array)$result);
                return response()->json(['message' => 'Gagal mendapatkan referensi pembayaran dari Duitku.'], 500);
            }
        } catch (\Exception $e) {
            // Jika server error (misal koneksi putus), batalkan juga
            $transaction->update(['status' => 'FAILED']);
            
            return response()->json(['message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function cancelPayment(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
        ]);

        // 1. Cari transaksi di database berdasarkan referensi/ID
        // Sesuaikan 'Transaction' dengan nama Model yang kamu gunakan
        $transaction = Transaction::where('reference_id', $request->reference)
                                    ->where('user_id', auth()->id()) // Pastikan milik user yang login
                                    ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.'
            ], 404);
        }

        // 2. Cek apakah statusnya masih PENDING
        // Kita tidak ingin membatalkan transaksi yang sudah LUNAS atau sudah BATAL
        if ($transaction->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ini tidak dapat dibatalkan (Status: ' . $transaction->status . ').'
            ], 400);
        }

        // 3. Update status menjadi CANCELED
        try {
            $transaction->update([
                'status' => 'CANCELED',
                'canceled_at' => now(), // Opsional: catat waktu pembatalan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan. Silakan buat pesanan baru.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status transaksi.'
            ], 500);
        }
    }

    // Fungsi ini dipanggil otomatis oleh Duitku saat user SELESAI BAYAR
    public function callback(Request $request)
    {
        $merchantCode = env('DUITKU_MERCHANT_CODE');
        $merchantKey = env('DUITKU_MERCHANT_KEY');
        
        $amount = $request->amount;
        $merchantOrderId = $request->merchantOrderId;
        $signature = $request->signature;
        $resultCode = $request->resultCode; // '00' berarti sukses
        $reference = $request->reference;

        // Validasi signature dari Duitku untuk keamanan
        $calcSignature = md5($merchantCode . $amount . $merchantOrderId . $merchantKey);

        if ($signature == $calcSignature) {
            if ($resultCode == "00") {
                $transaction = DuikuPOPPaymentTransaction::where('merchant_order_id', $merchantOrderId)->first();

                if ($transaction) {
                    // Duitku biasanya mengirim parameter 'fee' (biaya admin)
                    // dan 'amount' (total keseluruhan yang dibayar user)
                    $adminFee = (int) $request->input('fee', 0);
                    $totalAmount = (int) $request->input('amount');

                    $transaction->update([
                        'status' => 'SUCCESS',
                        'payment_method_code' => $request->paymentCode,
                        'total_amount' => $totalAmount, // Update dengan nominal final
                        'paid_amount' => $totalAmount,
                        'admin_fee' => $adminFee,       // Simpan biaya admin
                        'callback_payload' => $request->all(),
                        'paid_at' => now(),
                    ]);

                    // ... proses update status lunas di tabel installment ...
                }
                else{

                }
                
                return response('OK', 200);
            } else {
                Log::info($merchantOrderId);
                Log::info("duiku FAILED");
                // Pembayaran gagal / expired
                return response('FAILED', 400);
            }
        } else {
            // Signature tidak cocok (indikasi serangan / fake request)
            return response('BAD SIGNATURE', 400);
        }
    }
}