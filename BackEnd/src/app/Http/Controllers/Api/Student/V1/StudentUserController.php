<?php

namespace App\Http\Controllers\Api\Student\V1;

use App\Http\Controllers\Controller;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use App\Models\Contract;
use App\Models\Student;
use App\Models\StudentIdentity;
use App\Models\StudentLicense;
use App\Models\StudentBankAccount;
use App\Models\LpkCategory;
use App\Models\Notification;
use App\Models\Lpk;
use App\Models\NotificationStudent;
use App\Models\Payment;
use App\Models\DuikuPOPPaymentTransaction;
use Carbon\Carbon;
use LaravelJsonApi\Core\Responses\DataResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpWord\TemplateProcessor;

class StudentUserController extends Controller
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
     *
     *
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */
    public function profile(Request $request)
    {


        $student = auth()->user();
        Log::info(">>>>>>>>>>>> profile" . $student);
        // Log::info(">>>>>>>>>>>>".$student);
        // $student = Student::whereId(auth()->user()->id)->get();
        return new DataResponse(auth()->user());
        // return "dsad";
        // $success['attributes'] =  $student;
        // $response = [
        //     'jsonapi' => ['version'=>'1.0'],
        //     'success' => true,
        //     'data'    => $success
        // ];
        // return response()->json($response, 200);
    }

     /**
     *
     *
     * @return \Illuminate\Http\Response
     */

     public function UpdateJapanInformation(Request $request, Student $student)
     {
 
        //  Log::info("DATA UpdateJapanInformation >>>>>>>>>>>>");
        //  Log::info("DATA >>>>>>>>>>>>" . $request);
        //  Log::info($request->data);
         // $student = Student::find($request->data['id']);
         $student = auth()->user();
        //  Log::info("student >>>>>>>>>>>>");

        //  Log::info($student);
         if ($student) {
            //  Log::info("DATA STUDENTS >>>>>>>>>>>>" . $student);
             $student->student_kumiai_name = $request->data['attributes']['studentKumiaiName'];
             $student->student_kumiai_address = $request->data['attributes']['studentKumiaiAddress'];
             $student->student_employement_in_japan = $request->data['attributes']['studentEmployementInJapan'];
             $student->student_address_in_japan = $request->data['attributes']['studentAddressInJapan'];
             $student->student_work_program_in_japan = $request->data['attributes']['studentWorkProgramInJapan'] ?? null;
             $student->student_company_name_in_japan = $request->data['attributes']['studentCompanyNameInJapan'] ?? null;
             $student->save();
             return new DataResponse($student);
         } else {
             return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
         }
     }


    /**
     *
     *
     * @return \Illuminate\Http\Response
     */

    public function UpdateProfile(Request $request, Student $student)
    {

        // $student = Student::find($request->data['id']);
        // Log::info("ESSSSSSSSS" . json_encode($request->data));

        $student = auth()->user();
        if ($student) {
            // Log::info("self::handleFileUpload");
            // Log::info($request->data['attributes']);
            
            // Lakukan proses update di sini
            $student->student_name = $request->data['attributes']['studentName'];
            $student->student_address = $request->data['attributes']['studentAddress'];
            $student->student_email = $request->data['attributes']['studentEmail'];
            $student->student_place_of_birth_id = $request->data['attributes']['studentPlaceOfBirth'];
            $student->student_date_of_birth = $request->data['attributes']['studentDateOfBirth'];
            $student->student_gender = $request->data['attributes']['studentGender'];
            $student->student_blood_type = $request->data['attributes']['studentBloodType'];
            $student->student_religion = $request->data['attributes']['studentReligion'];
            $student->student_last_education = $request->data['attributes']['studentLastEducation'];
            $student->student_phone_no = $request->data['attributes']['studentPhoneNo'];
            $student->student_parent_name = $request->data['attributes']['studentParentName'];
            $student->student_parent_relationship = $request->data['attributes']['studentParentRelationship'];
            $student->student_parent_phone_no = $request->data['attributes']['studentParentPhoneNo'];
            // $student->student_phone_no = $request->data['attributes']['studentPhoneNo'];
            $student->student_emergency_phone_no = $request->data['attributes']['studentEmergencyPhoneNo'] ?? null;
            if (isset($request->data['attributes']["studentPhotoUrl"])) {
                self::handleFileUpload($student, 'studentAttachmentPhoto', $request->data['attributes']["studentPhotoUrl"]);
            }
            $student->save();

            $listIdIdentity = array();
            // Log::info(">>>>>>>>>> ['attributes']['studentIdentitys']");
            // Log::info($request->data['attributes']['studentIdentitys']);

            foreach ($request->data['attributes']['studentIdentitys'] as $value) {
                if (isset($value["id"])) {
                    $studentIdentity = StudentIdentity::find($value["id"]);
                    if ($studentIdentity && $studentIdentity->exists()) {
                        $studentIdentity->student_id = $student->id;
                        $studentIdentity->student_identity_type = $value["studentIdentityType"];
                        $studentIdentity->student_identity_no = $value["studentIdentityNo"];
                        $studentIdentity->student_identity_name_of_educational_institution = $value["studentIdentityNameOfEducationalInstitution"] ?? null;
                        $studentIdentity->student_identity_expire_date = $value["studentIdentityExpireDate"] ?? null;
                        $studentIdentity->student_identity_address = $value["studentIdentityAddress"] ?? null;
                        $studentIdentity->student_identity_place_of_issue = $value["studentIdentityPlaceOfIssue"] ?? null;
                        $studentIdentity->save();
                        if (isset($value["studentIdentityFile"])) {
                            // Log::info($value["studentIdentityFile"]);
                            self::handleFileUpload($studentIdentity, 'studentAttachmentIdentity', $value["studentIdentityFile"]);
                        }
                        array_push($listIdIdentity, $studentIdentity->id);
                    }
                } else {
                    $studentIdentity = new StudentIdentity;
                    $studentIdentity->student_id = $student->id;
                    $studentIdentity->student_identity_type = $value["studentIdentityType"];
                    $studentIdentity->student_identity_no = $value["studentIdentityNo"];
                    $studentIdentity->student_identity_name_of_educational_institution = $value["studentIdentityNameOfEducationalInstitution"] ?? null;
                    $studentIdentity->student_identity_expire_date = $value["studentIdentityExpireDate"] ?? null;
                    $studentIdentity->student_identity_address = $value["studentIdentityAddress"] ?? null;
                    $studentIdentity->student_identity_place_of_issue = $value["studentIdentityPlaceOfIssue"] ?? null;
                    $studentIdentity->save();
                    if (isset($value["studentIdentityFile"])) {
                        self::handleFileUpload($studentIdentity, 'studentAttachmentIdentity', $value["studentIdentityFile"]);
                    }
                    array_push($listIdIdentity, $studentIdentity->id);
                }
            }

            $listIdIdentityNotfound = StudentIdentity::where('student_id',$student->id)->whereNotIn('id', $listIdIdentity)->get('id');
            StudentIdentity::destroy($listIdIdentityNotfound);

            //   Log::info("======================");
            //   Log::info($request->data['attributes']['studentBankAccounts']);

            $listIdBankAccount = array();
            if (isset($request->data['attributes']['studentBankAccounts'])) {
                foreach ($request->data['attributes']['studentBankAccounts'] as $value) {
                    if (isset($value["id"])) {
                        $studentBankAccount = StudentBankAccount::find($value["id"]);
                    // Log::info($studentBankAccount);
                    // Log::info($value["studentBankAccountBankName"]);
                        if ($studentBankAccount && $studentBankAccount->exists()) {
                            $studentBankAccount->student_id = $student->id;
                            $studentBankAccount->student_bank_account_bank_name = $value["studentBankAccountBankName"];
                            $studentBankAccount->student_bank_account_no = $value["studentBankAccountNo"];
                            $studentBankAccount->student_bank_account_holder = $value["studentBankAccountHolder"];
                            array_push($listIdBankAccount, $studentBankAccount->id);
                            $studentBankAccount->save();
                        }
                    } else {
                        $studentBankAccount = new StudentBankAccount;
                        $studentBankAccount->student_id = $student->id;
                        $studentBankAccount->student_bank_account_bank_name = $value["studentBankAccountBankName"];
                        $studentBankAccount->student_bank_account_no = $value["studentBankAccountNo"];
                        $studentBankAccount->student_bank_account_holder = $value["studentBankAccountHolder"];
                        array_push($listIdBankAccount, $studentBankAccount->id);
                        $studentBankAccount->save();
                    }
                }
            }
            if (empty($listIdBankAccount)) {
                StudentBankAccount::where('student_id', $student->id)->delete();
            } else {
                $listIdBankAccountNotfound = StudentBankAccount::where('student_id',$student->id)->whereNotIn('id', $listIdBankAccount)->get('id');
                StudentBankAccount::destroy($listIdBankAccountNotfound);
            }
            // Setelah proses update selesai, Anda bisa memberikan respons yang sesuai
            return new DataResponse($student);
        } else {
            // Jika data tidak ditemukan, kembalikan respons dengan status 404 dan pesan 'Data not found'
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    // Image

    /**
     * @param string $base64EncodedString
     * @return string
     */
    public static function getExtension(string $base64EncodedString): string
    {
        if (str_contains($base64EncodedString, 'base64')) {
            $extension = explode(";", explode("/", $base64EncodedString)[1])[0];
            switch ($extension) {
                case 'image/jpeg':
                case 'jpeg':
                    return ".jpeg";
                case 'image/jpg':
                case 'jpg':
                    return ".jpg";
                case 'image/png':
                case 'png':
                    return '.png';
                case 'image/gif':
                case 'gif':
                    return '.gif';
                case 'application/pdf':
                case 'pdf':
                    return '.pdf';
                default:
                    return "";
            }
        } else {
            return self::detectMimeType($base64EncodedString);
        }
    }


    /**
     * @param string $base64
     * @return string
     */
    static function detectMimeType(string $base64)
    {
        $signaturesForBase64 = [
            // 'JVBERi0'     => "application/pdf",
            // 'R0lGODdh'    => "image/gif",
            // 'R0lGODlh'    => "image/gif",
            // 'iVBORw0KGgo' => "image/png",
            // '/9j/'        => "image/jpeg"
            'JVBERi0'     => ".pdf",
            'R0lGODdh'    => ".gif",
            'R0lGODlh'    => ".gif",
            'iVBORw0KGgo' => ".png",
            '/9j/'        => ".jpeg"

        ];

        foreach ($signaturesForBase64 as $sign => $mimeType) {
            if (strpos($base64, $sign) === 0) {
                return $mimeType;
            }
        }
        return false;
    }

    /**
     * @throws JsonApiException
     */
    private static function handleFileUpload($model, string $collection, $imageBase64)
    {
        try {
            if (!isset($imageBase64)) return;
            try {
                $extension = self::getExtension($imageBase64);
            } catch (\Exception $exception) {
                $extension = '';
            }
            $filename = Str::random() . $extension;
            $model->addMediaFromBase64($imageBase64, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'application/pdf'])->usingFileName($filename)->toMediaCollection($collection);

            // Log::info($filename);
            $model->save();
        } catch (InvalidBase64Data | FileCannotBeAdded | FileDoesNotExist | FileIsTooBig | MimeTypeNotAllowed $exception) {
            $error = Error::fromArray([
                'title' => 'Invalid File',
                'detail' => $exception->getMessage(),
                'status' => 422,
            ]);
            throw new JsonApiException($error);
        }
    }



     
      /**
     * History payment for a given id.
     * Menggabungkan manual payment + Duitku SUCCESS transaction
     * difilter berdasarkan student yang login.
     */
    public function historyPayment()
    {
        $studentId = auth()->id();

        $paymentMethods = [
            1 => 'Cash',
            2 => 'Transfer',
            3 => 'Debit',
            4 => 'Virtual Account',
            5 => 'Duitku Gateway',
        ];

        $manualPayments = Payment::with(['paymentUse.installment_relation', 'paymentCashier'])
            ->where('payment_student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($payment) use ($paymentMethods) {
                return [
                    'type' => 'manual',
                    'id'   => $payment->id,
                    'attributes' => [
                        'transaction_no' => $payment->payment_no,
                        'amount'         => (int) $payment->payment_receive_amount,
                        'method'         => $paymentMethods[$payment->payment_method] ?? 'Unknown',
                        'method_code'    => $payment->payment_method,
                        'cashier_name'   => $payment->paymentCashier->name ?? null,
                        'payment_at'     => $payment->payment_at,
                        'created_at'     => $payment->created_at->toISOString(),
                        'remark'         => $payment->payment_remark,
                        'installments'   => $payment->paymentUse->map(fn($pu) => [
                            'installment_id' => $pu->installment_id,
                            'installment_no' => $pu->installment ? $pu->installment['installment_no'] : null,
                            'amount_used'    => (int) $pu->payment_use,
                        ])->values(),
                    ],
                ];
            });

        $duitkuPayments = DuikuPOPPaymentTransaction::with('paymentUse.installment_relation')
            ->where('student_id', $studentId)
            ->where('status', 'SUCCESS')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'duitku',
                    'id'   => $payment->id,
                    'attributes' => [
                        'transaction_no' => $payment->merchant_order_id,
                        'amount'         => (int) $payment->total_amount,
                        'base_amount'    => (int) $payment->base_amount,
                        'admin_fee'      => (int) $payment->admin_fee,
                        'paid_amount'    => (int) $payment->paid_amount,
                        'method'         => $payment->payment_method_name,
                        'method_code'    => $payment->payment_method_code,
                        'paid_at'        => $payment->paid_at ? $payment->paid_at->toISOString() : null,
                        'created_at'     => $payment->created_at->toISOString(),
                        'installments'   => $payment->paymentUse->map(fn($pu) => [
                            'installment_id' => $pu->installment_id,
                            'installment_no' => $pu->installment ? $pu->installment['installment_no'] : null,
                            'amount_used'    => (int) $pu->payment_use,
                        ])->values(),
                    ],
                ];
            });

        $history = $manualPayments->concat($duitkuPayments)
            ->sortByDesc(fn($item) => $item['attributes']['created_at'])
            ->values();

        return response()->json([
            'data' => $history,
        ]);
    }

    

      /**
     * contract for a given id.
     */
    public function contract()

    {
        
        if(auth()->user()->studentContractActive){
            // Log::info(auth()->user()->studentContractActive->first());
            // $contract = Contract::find(auth()->user()->studentContractActive->id);
            // Log::info($contract);
            return new DataResponse(auth()->user()->studentContractActive);
          }
          else{
            // Log::info("bas");
            return new DataResponse([]);
          }
    }

      /**
     * Installment for a given id.
     */
    public function installment()

    {
        if(auth()->user()->studentContractActive){
          return new DataResponse(auth()->user()->studentContractActive->contractInstallment);
        }
        else{
            return new DataResponse([]);
        }
    }

     /**
     * active Installment for a given id.
     */
    public function installmentActive()

    {
        if(auth()->user()->studentContractActive){
            return new DataResponse(auth()->user()->studentContractActive->installmentActive);
          }
          else{
              return new DataResponse([]);
          }
    }

      /**
     * identity for a given id.
     */
    public function identity()

    {
        return new DataResponse(auth()->user()->studentIdentitys);
    }

      /**
     * bankAccount for a given id.
     */
    public function bankAccount()

    {
        return new DataResponse(auth()->user()->studentBankAccounts);
    }


      /**
     * notification.
     */
    public function notificationList()

    {

        $notifications = auth()->user()->studentNotification;

        $filteredNotifications = $notifications->filter(function ($notification) {
            return $notification->notification_at_utc < now();
        });


        // return new DataResponse(auth()->user()->studentNotification->where('notification_at','<',now()));
        return new DataResponse($filteredNotifications->values());
    
    }


      /**
     * notification for a given notification id.
     */
    public function notificationGet($id)

    {
        $notifications = auth()->user()->studentNotification()->where('notifications.id', $id)->get();
        return new DataResponse($notifications);
    }


     /**
     * notification content for a given notification id.
     */
    public function notificationContent($id)

    {
       
        $notifications = auth()->user()->studentNotification()->where('notifications.id', $id)
        ->with('notificationContent')->get();
        // Log::info($notifications->pluck('notificationContent'));
        return new DataResponse($notifications->pluck('notificationContent'));
    }


         /**
     * notification read for a given notification id.
     */
    public function notificationRead($id)

    {
        // Log::info($id);
        $notificationsStudent = NotificationStudent::where('notification_id',$id)->where('student_id',auth()->user()->id)->first();
        if( $notificationsStudent){
            $notificationsStudent->status_read = 1;
            $notificationsStudent->save();
        }

        return new DataResponse(auth()->user()->studentNotification()->where('notifications.id', $id)->get());
    }

    // download start

     /**
     *
     *
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */

    public function downloadDocSuratPersetujuanOrangTua(Request $request, Student $student) {

        $data = auth()->user();
        // $student = auth()->user();
        // Log::info(new DataResponse(auth()->user()));
        // Log::info(">>>>>>>>>>>> downloadDocSuratPersetujuanOrangTua" . $data);
        // $student = Student::whereId(auth()->user()->id)->get();
       
        $lpk = Lpk::find($data->lpk_id);
        // Log::info($data->student_employement_in_japan);
        $studentEmployement = LpkCategory::where('id', $data->student_employement_in_japan)->first(); // Mengambil data LPK berdasarkan lpkId
        // Log::info($studentEmployement->category_name);
        // Log::info("---------- TEST BANG CATEGORY --------------");


        if (!$lpk) {
            return response()->json(['error' => 'Lpk not found'], 404); // Jika LPK tidak ditemukan
        }
        if (!$data) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        if ($data) {
            $student = $data;
            $studentName = $student->student_name;
            // $studentNo = $student->student_no;
            // $studentEmployment = $student->lpkCategory->category->category_name ?? 'N/A';
            Carbon::setLocale('id');
        
            // Get identity type and student birth date
            $identityType1 = $student->studentIdentitys->firstWhere('student_identity_type', 1);
            $identityNo = $identityType1 ? $identityType1->student_identity_no : 'N/A';
            $studentDateOfBirth = Carbon::parse($student->student_date_of_birth);
            $formattedDateOfBirth = $studentDateOfBirth->translatedFormat('d F Y');
        
            // Initialize TemplateProcessor
            $templateProcessor = new TemplateProcessor(base_path('resources/lpk/doc/template_parental_approval_letter.docx'));
            // Log::info("---------- TEST BANG --------------");
            // Log::info($lpk);
        
            // Get current date in Indonesian format
            $currentDate = Carbon::now();
            $day = $currentDate->day;
            $month = $currentDate->translatedFormat('F');
            $year = $currentDate->year;
        
            // Replace placeholders with actual values
            $templateProcessor->setValue('lpkName', $lpk->lpk_name);
            $templateProcessor->setValue('studentName', $studentName);
            // template_parental_approval_letter.docx
            // $templateProcessor->setValue('studPlaceOfBirth' , $cityName );
            // $templateProcessor->setValue('studDateOfBirth' , $formattedDateOfBirth);
            $templateProcessor->setValue('day', $day);
            $templateProcessor->setValue('month', $month);
            $templateProcessor->setValue('year', $year);
            $templateProcessor->setValue('studentAddress', $student->student_address);
            $templateProcessor->setValue('studentParentName', $student->student_parent_name);
            $templateProcessor->setValue('studentEmployment', $studentEmployement->category_name);
        
            // Output the document
            ob_start();
            $templateProcessor->saveAs("php://output");
            $contents = ob_get_contents();
            ob_end_clean();
        
            return response()->streamDownload(function () use ($contents) {
                echo $contents;
            }, str_replace(['/', ' '], '_', $studentName . '-' . $student->student_no) . '.docx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
                'Content-Disposition' => 'attachment; filename="'.str_replace(['/', ' '], '_', $studentName).'.docx"',
            ]);
        }
        
    }

      /**
     *
     *
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */

     public function downloadFileAttachment(Request $request, Student $student)
     {
     
         $notificationId = $request->route('notificationId');
         $fileId = $request->route('fileId');
         $fileName = $request->route('fileName');
     
         $filePath = public_path('storage/uploads/notification_attachment/' . $notificationId . '/' . $fileId . '/' . $fileName);
         
         Log::info("File Path: " . $filePath);
     
         if (!file_exists($filePath)) {
             Log::info("File does not exist: " . $filePath);
             return response()->json(['error' => 'File not found.'], 404);
         }
     
         $studentName = str_replace(['/', ' '], '_', $student->name . '-' . $student->student_no);
         $downloadFileName = $studentName . '.docx'; // Ganti sesuai kebutuhan
     
         return response()->download($filePath, $fileName, [
             'Content-Type' => 'image/png', 
             'Access-Control-Expose-Headers' => 'Content-Disposition',
             'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
         ]);
     }
     
    // download end

    /**
     *
     *
     * @return \Illuminate\Http\Response
     */

     public function updateRequiredDocktpPemohonPenjamin(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDocktpPemohonPenjamin"])) {
                self::handleFileUpload($student, 'ktpPemohonPenjamin', $request->data['attributes']["requiredDocktpPemohonPenjamin"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDockartuKeluarga(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDockartuKeluarga"])) {
                self::handleFileUpload($student, 'kartuKeluarga', $request->data['attributes']["requiredDockartuKeluarga"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocIjazahSekolah(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDocijazahSekolah"])) {
                self::handleFileUpload($student, 'ijazahSekolah', $request->data['attributes']["requiredDocijazahSekolah"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocFotoKeluarga(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDocfotoKeluarga"])) {
                self::handleFileUpload($student, 'fotoKeluarga', $request->data['attributes']["requiredDocfotoKeluarga"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocKontrakPerjanjianHirePurchase(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDockontrakPerjanjianHirePurchase"])) {
                self::handleFileUpload($student, 'kontrakPerjanjianHirePurchase', $request->data['attributes']["requiredDockontrakPerjanjianHirePurchase"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocSuratPermohonanHirePurchase(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDocsuratPermohonanHirePurchase"])) {
                self::handleFileUpload($student, 'suratPermohonanHirePurchase', $request->data['attributes']["requiredDocsuratPermohonanHirePurchase"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocSuratKesanggupanBayar(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDocsuratKesanggupanBayar"])) {
                self::handleFileUpload($student, 'suratKesanggupanBayar', $request->data['attributes']["requiredDocsuratKesanggupanBayar"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocHasilPraMedicalCheckUp(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDochasilPraMedicalCheckUp"])) {
                self::handleFileUpload($student, 'hasilPraMedicalCheckUp', $request->data['attributes']["requiredDochasilPraMedicalCheckUp"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocNameTagLpk(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDocnameTagLpk"])) {
                self::handleFileUpload($student, 'nameTagLpk', $request->data['attributes']["requiredDocnameTagLpk"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocCertificateOfEligibility(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDoccertificateOfEligibility"])) {
                self::handleFileUpload($student, 'certificateOfEligibility', $request->data['attributes']["requiredDoccertificateOfEligibility"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocSurpervisingOrganization(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDocsurpervisingOrganization"])) {
                self::handleFileUpload($student, 'surpervisingOrganization', $request->data['attributes']["requiredDocsurpervisingOrganization"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocKontrakKerjaDiJepang(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDockontrakKerjaDiJepang"])) {
                self::handleFileUpload($student, 'kontrakKerjaDiJepang', $request->data['attributes']["requiredDockontrakKerjaDiJepang"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocTiketPesawatKeberangkatan(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDoctiketPesawatKeberangkatan"])) {
                self::handleFileUpload($student, 'tiketPesawatKeberangkatan', $request->data['attributes']["requiredDoctiketPesawatKeberangkatan"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocFormulirPendaftaranKoperasi(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDocformulirPendaftaranKoperasi"])) {
                self::handleFileUpload($student, 'formulirPendaftaranKoperasi', $request->data['attributes']["requiredDocformulirPendaftaranKoperasi"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }

    public function updateRequiredDocSuratPersetujuanOrangTua(Request $request, Student $student)
    {
        $student = auth()->user();
        if ($student) {
            if (isset($request->data['attributes']["requiredDocsuratPersetujuanOrangTua"])) {
                self::handleFileUpload($student, 'suratPersetujuanOrangTua', $request->data['attributes']["requiredDocsuratPersetujuanOrangTua"]);
            }
            return new DataResponse($student);
        } else {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }
    }




     
     


}

