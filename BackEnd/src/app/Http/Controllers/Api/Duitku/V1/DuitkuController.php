<?php

namespace App\Http\Controllers\Api\Duitku\V1;

use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use Illuminate\Support\Facades\Log;
use App\Models\Lpk;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class DuitkuController extends Controller
{
    
    public function inquiry(){
      // variable data Merchant kita
      $actionWe = 'inquiry';

      $merchantCodeWe = env('DUITKU_MERCHANT_CODE');
      $apiKeyWe = env('DUITKU_API_KEY');

      // variable untuk menampung data yang dikirim dari duitku
      $action = $request->action;
      $merchantCode = $request->merchantCode;
      $bin = $request->bin;
      $vaNo = $request->vaNo;
      $session = $request->session;
      $signature = $request->signature;

      // variable untuk kondisi default ketika tidak ada data yang ditemukan
      $vaNoDb = null;
      $noVaDriverFinal = null;

      // nomor prefix
      $prefixCIMB = env('DUITKU_PREFIX_CIMB');

      //fee 

      // memisahkan nomor prefix (6 Digit) dengan nomor VA (Virtual Account) dari duitku
      // CONTOH : 88521(Prefix)9537462(VA dari Nomor Driver) jadi returnnya hanya Nomor 9537462
      // $onlyVA = substr($vaNo, 6);

      // kodisi pemisah nomor untuk pencarian nomor Driver contoh dari 9537462 hanya diambil 95 ditambah 08 didepan jadi 0895
      // $noAwal = '08' . substr($onlyVA, 0, 2);
      // dari 9537462 diambil 5 angka dari belakang jadi 37462
      // $noAkhir = substr($onlyVA, -8);

      // kondisi untuk cek Virtual Account ada atau tidaknya pada data table driver yang mana TAXI atau Black Plat
      // kondisi pencarian dari No. Awal nomor Driver dan No. Akhir nomor Driver
      // CONTOH : dari variable $noAwal 0895 dan dari variable $noAkhir 37462



      //update fungsi untuk mencocokan no va student yang aktif
      $dataStudent = Student::where('duitku_va', substr($vaNo, -10))->where('status',1)->first();

      // lihat prefix
      $prefixDariDuitku = substr($vaNo, 0, -10);
      $prefixFinal = null;

      if ($prefixDariDuitku == $prefixCIMB) {
          $prefixFinal = $prefixCIMB;
      }
  


      $driver_business_type = null;

      if ($dataStudent) {
          // $noVaDriverFinal = $prefixFinal . $dataDataDriverGet->duitku_va;
          $noVaDriverFinal = $dataDataDriverGet->duitku_va;
      }

      // menggabungkan bin ,prefix, dan nomor Dirver untuk validasi kondisi signature jika noVa itu benar dengan data yang kita punya
      // CONTOH dari $bin (1199), $prefix (88521), dan $noVaDriverFinal (9537462) jadi 1199-88521-9537462
      // $noVaSignatureWe = $prefix . $noVaDriverFinal;
      $noVaSignatureWe = $noVaDriverFinal;
      // Log::info('$noVaSignatureWe ' . $noVaSignatureWe);
      // buat signature untuk keamanan system
      $signatureWe = md5($merchantCodeWe . $action . $noVaSignatureWe . $session . $apiKeyWe);
      // Log::info('$signatureWe ' . $signatureWe);
      // kondisi jika signatur milik kita dan dari duitku sama
      if ($signature == $signatureWe) {
          $name_business_type = null;
          if ($driver_business_type == 1) {
              $name_business_type = "TAXI";
          } else {
              $name_business_type = "RENTAL";
          }

          // data yang akan dikeluarkan untuk response ke duitku
          $merchantOrderId = 'LPK-VA' . $dataStudent->id . time();
          $responseData = array(
              'vaNo' => $vaNo,
              'name' => $dataStudent->student_name,
              'amount' => 0,
              'merchantOrderId' => $merchantOrderId,
              'statusCode' => '00',
              'statusMessage' => 'SUCCESS',
          );

          // insert transaksi ke table untuk penyesuaian callback yang akan dikirimkan Duitku
          // status = 0 wating
          //  status = 1 succes
          //  status = 2 error

          VaPayment::create([
              'payment_gateway' => 'Duitku',
              'student_id' => $dataStudent->id,
              'no_va' => $vaNo,
              'merchant_order_id' => $merchantOrderId,
              'status' => 0,
              'inquiry_at' => now(),
          ]);
      } else {
          $responseData = array(
              'statusCode' => '01',
              'statusMessage' => 'NOT FOUND',
          );
      }

      return response()->json($responseData);
    }


    public function callback(){
        // Log::info('Duitku callback');
        // Log::info($request);
        $merchantCodeWe = env('DUITKU_MERCHANT_CODE');
        $apiKeyWe = env('DUITKU_API_KEY');

        // variable untuk menampung data callback yang dikirim oleh duitku 
        $merchantCode = $request->merchantCode;
        $amount = $request->amount;
        $merchantOrderId = $request->merchantOrderId;
        $productDetail = $request->productDetail;
        $additionalParam = $request->additionalParam;
        $paymentCode = $request->paymentCode;
        $signature = $request->signature;
        $resultCode = $request->resultCode;
        $merchantUserId = $request->merchantUserId;
        $reference = $request->reference;

        // Variable untuk fee
        $feeVa = null;
        // $feeServiceGms = 1000;
        $feeServiceGms = 0;

        // Mengkondisikan detail payment 
        if ($paymentCode == 'BT') {
            $detailMethod = 'Permata Virtual Account';
        } elseif ($paymentCode == 'VA') {
            $detailMethod = 'Maybank Virtual Account';
        } elseif ($paymentCode == 'B1') {
            $detailMethod = 'CIMB Niaga Virtual Account';
            // $feeVa = 3000;
            $feeVa = 0;
        } elseif ($paymentCode == 'M2') {
            $detailMethod = 'Mandiri Virtual Account';
        } elseif ($paymentCode == 'BC') {
            $detailMethod = 'BCA Virtual Account';
        } elseif ($paymentCode == 'AG') {
            $detailMethod = 'Bank Artha Graha';
        } elseif ($paymentCode == 'S1') {
            $detailMethod = 'Bank Sahabat Sampoerna';
        } elseif ($paymentCode == 'BR') {
            $detailMethod = 'BRI Virtual Account';
        } else {
            $detailMethod = '';
        }

        // Mencari OrderId dari table fms_va
        // $merchantOrderIdDriver =  FmsVaPayments::where('merchant_orderId', $merchantOrderId)->first();

        // signature untuk kemanan system
        $signatureWe = md5($merchantCodeWe . $amount . $merchantOrderId . $apiKeyWe);

        // kondisi jika signatur milik kita dan dari duitku sama
        if ($signature == $signatureWe) {

            // untuk mengupdate table fms_va setelah transaksi sampai ke akhir
            // status_payment_va = 0 wating
            //  status_payment_va = 1 succes
            //  status_payment_va = 2 error
            // FmsVaPayments::where('merchant_orderId', $merchantOrderId)
            //     ->update([
            //         'amount' => $amount,
            //         'payment_code' => $paymentCode . '-' . $detailMethod,
            //         'status_payment_va' => $resultCode == "00" ? 1 : 2,
            //         'reference' => $reference,
            //     ]);

            $VaPayment = VaPayment::where('payment_gateway', 'Duitku')
            ->where('merchant_order_id', $merchantOrderId)->first();
            // Log::info($resultCode);
            if ($VaPayment) {
                $FmsVaPayments->amount = $amount;
                $FmsVaPayments->fee_amount = $feeVa;
                $FmsVaPayments->fee_service = $feeServiceGms;
                $FmsVaPayments->payment_code = $paymentCode . '-' . $detailMethod;
                $FmsVaPayments->status = ($resultCode == "00") ? 1 : 2;
                $FmsVaPayments->callback_at = now();
                $FmsVaPayments->reference = $reference;
                $FmsVaPayments->save();
            } else {
               return response()->json(['statusCode' => '01','statusMessage' => 'NOT FOUND']);
            }



            // variable yang disimpan dari callback 
            $notif =  array(
                'merchantCode' => $merchantCode,
                'amount' => $amount,
                'merchantOrderId' => $merchantOrderId,
                'productDetail' => $productDetail,
                'additionalParam' => $additionalParam,
                'paymentCode' => $paymentCode . '-' . $detailMethod,
                'signature' => $signature,
                'resultCode' => $resultCode,
                'merchantUserId' => $merchantUserId,
                'reference' => $reference,
            );

            // return response()->json(['message' => 'SUCCESS']);
            $responseData = array(
                'statusCode' => '00',
                'statusMessage' => 'SUCCESS',
            );

            return response()->json($responseData);
        }

        return response()->json(['statusCode' => '01','statusMessage' => 'NOT FOUND']);
    }


}
