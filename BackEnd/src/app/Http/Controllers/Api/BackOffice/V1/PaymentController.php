<?php

namespace App\Http\Controllers\Api\BackOffice\V1;

use App\Http\Controllers\Controller;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use App\JsonApi\BackOffice\V1\Payments\PaymentQuery;
use App\JsonApi\BackOffice\V1\Payments\PaymentRequest;
use App\JsonApi\BackOffice\V1\Payments\PaymentSchema;
use App\Models\Payment;
use App\Models\Contract;
use Illuminate\Support\Facades\Log;
use LaravelJsonApi\Core\Responses\DataResponse;
use App\JsonApi\BackOffice\V1\Payments\PaymentCollectionQuery;
use Carbon\Carbon;
use App\Imports\PaymentsImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class PaymentController extends Controller
{

    use Actions\FetchMany;
    use Actions\FetchOne;
    use Actions\Store;
    use Actions\Update;
    use Actions\Destroy;
    use Actions\FetchRelated;
    use Actions\FetchRelationship;
    use Actions\UpdateRelationship;
    use Actions\AttachRelationship;
    use Actions\DetachRelationship;



    /**
     * Create a new resource.
     *
     * @param PaymentSchema $schema
     * @param PaymentRequest $request
     * @param PaymentQuery $query
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */
    public function store(PaymentSchema $schema, PaymentRequest $request)
    {

        $payment = new Payment;
        $payment->payment_no = IdGenerator::generate(['table' => 'payments','field'=>'payment_no', 'length' => 15, 'prefix' =>  'PAYMENT/' . date('Y') . '/' . date('m') . '/', 'reset_on_prefix_change' => true]);
        $payment->payment_contract_id = $request->data['attributes']['paymentContract'];
        $payment->payment_student_id = $request->data['attributes']['paymentStudent'];
        $payment->payment_method = $request->data['attributes']['paymentMethod'];
        $payment->payment_receive_amount = $request->data['attributes']['paymentReceiveAmount'];
        $payment->payment_remark = $request->data['attributes']['paymentRemark'];
        $payment->payment_transfer_no = isset($request->data['attributes']['paymentTransferNo'])?$request->data['attributes']['paymentTransferNo']:null;
        $payment->payment_at = $request->data['attributes']['paymentAt'];
        $payment->payment_cashier_id = auth()->user()->id;
        $payment->save();

        return new DataResponse($payment);
    }


    /**
     * Update a resource.
     *
     * @param PaymentSchema $schema
     * @param PaymentRequest $request
     * @param PaymentQuery $query
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */
    public function Update(PaymentSchema $schema, PaymentRequest $request, Payment $payment)
    {


        $payment->payment_method = $request->data['attributes']['paymentMethod'];
        $payment->payment_receive_amount = $request->data['attributes']['paymentReceiveAmount'];
        $payment->payment_remark = $request->data['attributes']['paymentRemark'];
        $payment->payment_transfer_no = isset($request->data['attributes']['paymentTransferNo'])?$request->data['attributes']['paymentTransferNo']:null;
        $payment->payment_at = $request->data['attributes']['paymentAt'];
        $payment->save();



        return new DataResponse($payment);
    }

//     public function import(Request $request)
// {
//     // Validate the request to ensure a file is uploaded
//     $request->validate([
//         'file' => 'required|file|mimes:xlsx,csv,xls', // Adjust the mime types as needed
//     ]);

//     $file = $request->file('file');

//     // Import the file using the PaymentsImport class
//     try {
//         Excel::import(new PaymentsImport, $file);
//         return response()->json(['message' => 'Import successful'], 200);
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }

// public function import(Request $request)

//     {
//         log:info($request);
//         // Validate incoming data (e.g., make sure 'data' is provided)
//         $request->validate([
//             'data' => 'required|array', // 'data' field is required and must be an array
//         ]);

//         // Loop through the data and insert into the database
//         foreach ($request->input('data') as $paymentData) {
//             Payment::create([
//                 'payment_no' => $paymentData['payment_no'],
//                 'payment_student_id' => $paymentData['payment_student_id'],
//                 'payment_contract_id' => $paymentData['payment_contract_id'],
//                 'payment_method' => $paymentData['payment_method'],
//                 'payment_receive_amount' => $paymentData['payment_receive_amount'],
//                 'payment_change_amount' => $paymentData['payment_change_amount'],
//                 'payment_remark' => $paymentData['payment_remark'],
//                 'payment_cashier_id' => $paymentData['payment_cashier_id'],
//                 'payment_transfer_no' => $paymentData['payment_transfer_no'],
//                 'payment_at' => $paymentData['payment_at'],
//             ]);
//         }

//         // Return a success response
//         return response()->json(['message' => 'Payments imported successfully.']);
//     }

// }

public function import(Request $request)
{
    // Validate incoming data (check if 'data' exists and is an array)
    $request->validate([
        'data' => 'required|array', // 'data' field is required and must be an array
    ]);

    // Get the array of payment data from the request
    $data = $request->input('data');

    // Log the incoming data for debugging purposes
    Log::info('Received Payment Data:', ['data' => $data]);

    DB::beginTransaction();  // Start transaction to ensure data integrity

    try {
        // Loop through each row of data and insert it into the payments table
        foreach ($data as $paymentData) {
            // Extract the attributes from the nested structure
            $paymentAttributes = $paymentData['attributes'];

            // Insert payment data, using default values for optional fields
            Payment::create([
                'payment_no' => $paymentAttributes['payment_no'],
                'payment_student_id' => $paymentAttributes['payment_student_id'],
                'payment_contract_id' => $paymentAttributes['payment_contract_id'],
                'payment_method' => $paymentAttributes['payment_method'] ?? '',
                'payment_receive_amount' => $paymentAttributes['payment_receive_amount'],
                'payment_change_amount' => $paymentAttributes['payment_change_amount'] ?? 0,
                'payment_remark' => $paymentAttributes['payment_remark'] ?? '',
                'payment_cashier_id' => $paymentAttributes['payment_cashier_id'],
                'payment_transfer_no' => $paymentAttributes['payment_transfer_no'] ?? '',
                'payment_at' => $paymentAttributes['payment_at'],
            ]);
        }

        DB::commit(); // Commit the transaction if everything is successful
        return response()->json(['message' => 'Payments imported successfully.'], 200);

    } catch (\Exception $e) {
        DB::rollBack(); // Rollback transaction in case of error
        Log::error('Error during import: ' . $e->getMessage());
        return response()->json(['message' => 'Import failed.', 'error' => $e->getMessage()], 500);
    }
}
}
