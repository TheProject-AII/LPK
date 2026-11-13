<?php

namespace App\Http\Controllers\Api\BackOffice\V1;

use App\Http\Controllers\Controller;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use App\JsonApi\BackOffice\V1\Contracts\ContractQuery;
use App\JsonApi\BackOffice\V1\Contracts\ContractRequest;
use App\JsonApi\BackOffice\V1\Contracts\ContractUpdateStatusRequest;
use App\JsonApi\BackOffice\V1\Contracts\ContractUploadDocRequest;
use App\JsonApi\BackOffice\V1\Contracts\ContractSchema;
use App\Models\Contract;
use App\Models\User;
use App\Models\ContractStatus;
use App\Models\StudentStatus;
use App\Models\VehicleStatus;
use App\Models\Student;
use LaravelJsonApi\Core\Responses\DataResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use \NumberFormatter;
use App\Notifications\ContractInfoNotification;


class ContractController extends Controller
{

    use Actions\FetchMany;
    use Actions\FetchOne;
    // use Actions\Store;
    // use Actions\Update;
    use Actions\Destroy;
    use Actions\FetchRelated;
    use Actions\FetchRelationship;
    use Actions\UpdateRelationship;
    use Actions\AttachRelationship;
    use Actions\DetachRelationship;


    /**
     * Create a new resource.
     *
     * @param ContractSchema $schema
     * @param ContractRequest $request
     * @param ContractQuery $query
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */

     public function documentContract(ContractSchema $schema, Contract $contract) {

        if (!$contract) {
            return response()->json(['error' => 'Contract not found'], 404);
        }

        if ($contract) {
            $student = $contract->contractStudent;
            $lpk = $contract->contractLpk;
            $studentName = $student->student_name;
            $contractNo = $contract->contract_no;
            $studentDateOfBirth = Carbon::parse($student->student_date_of_birth);
            $indonesia_currency = NumberFormatter::create('id', NumberFormatter::CURRENCY);
            $identityType6 = $student->studentIdentitys->firstWhere('student_identity_type', 6);
            $identityNo = $identityType6 ? $identityType6->student_identity_no : 'N/A';
            $studentEmployment = $student->lpkCategory->category->category_name ?? 'N/A';


            Carbon::setLocale('id');
            $currentDate = Carbon::now();

            $formattedDateOfBirth = $studentDateOfBirth->translatedFormat('d F Y');
            $dayDate = $currentDate->translatedFormat('l'); // Day name in Indonesian
            $monthDate = $currentDate->translatedFormat('F'); // Month name in Indonesian
            $yearDate = $currentDate->format('Y'); // Year
            $cityName = $student->studentplaceOfBirth ? $student->studentplaceOfBirth->city_name : 'N/A';

            $templateProcessor = new TemplateProcessor(base_path('resources/lpk/doc/perjanjian_siswa.docx'));

            // Replace placeholders with actual values
            $templateProcessor->setValue('contractNo' , $contractNo);
            $templateProcessor->setValue('studentName', $studentName);
            $templateProcessor->setValue('dayDate' , $dayDate);
            $templateProcessor->setValue('monthDate' , $monthDate);
            $templateProcessor->setValue('yearDate' , $yearDate);
            $templateProcessor->setValue('placeOfBirth' , $cityName );
            $templateProcessor->setValue('dateOfbirth' , $formattedDateOfBirth);
            $templateProcessor->setValue('studentNation' , 'Indonesia');
            $templateProcessor->setValue('studentEmployment' , $studentEmployment);
            $templateProcessor->setValue('studentParentName' , $student->student_parent_name);
            $templateProcessor->setValue('studentParentRelationship' , $student->student_parentRelationship);
            $templateProcessor->setValue('studentAddressInIndonesia' , $student->student_address);
            $templateProcessor->setValue('studentAdressInJapan' , $student->student_address_in_japan);
            $templateProcessor->setValue('nominalPrinciple' , str_replace("X","",$indonesia_currency->format($contract->contract_principle)));
            $templateProcessor->setValue('lpkBankAccountNumber' , $lpk->lpk_bank_account_number);
            $templateProcessor->setValue('lpkBankAccountName' , $lpk->lpk_bank_account_name);
            $templateProcessor->setValue('lpkBankBranch' , $lpk->lpk_bank_branch);
            $templateProcessor->setValue('studentPassportNo', $identityNo);

            ob_start();

            $templateProcessor->saveAs("php://output");

            $contents = ob_get_contents();

            ob_end_clean();

            return response()->streamDownload(function () use ($contents) {
                echo $contents;
            }, str_replace(['/', ' '], '_', $studentName . '-' . $contractNo) . '.docx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
                'Content-Disposition' => 'attachment; filename="'.str_replace(['/', ' '], '_', $studentName . '-' . $contractNo. '-Document-Contract').'.docx"',
            ]);
        }
    }

    public function documentNewMemberRegistration(ContractSchema $schema, Contract $contract) {

        if (!$contract) {
            return response()->json(['error' => 'Contract not found'], 404);
        }

        if ($contract) {
            $student = $contract->contractStudent;
            $studentName = $student->student_name;
            $contractNo = $contract->contract_no;
            $studentDateOfBirth = Carbon::parse($student->student_date_of_birth);
            $identityType1 = $student->studentIdentitys->firstWhere('student_identity_type', 1);
            $identityNo = $identityType1 ? $identityType1->student_identity_no : 'N/A';
            $genderMap = [
                1 => 'Laki-laki',
                2 => 'Perempuan'
            ];
            $studentGender = $genderMap[$student->student_gender] ?? 'Unknown';
            $studentEmployment = $student->lpkCategory->category->category_name ?? 'N/A';


            Carbon::setLocale('id');
            $currentDate = Carbon::now()->translatedFormat('d F Y');;

            $formattedDateOfBirth = $studentDateOfBirth->translatedFormat('d F Y');
            $cityName = $student->studentplaceOfBirth ? $student->studentplaceOfBirth->city_name : 'N/A';

            $templateProcessor = new TemplateProcessor(base_path('resources/lpk/doc/template_formulir_pendaftaran_koperasi.docx'));

            // Replace placeholders with actual values
            $templateProcessor->setValue('contractNo' , $contractNo);
            $templateProcessor->setValue('studentName', $studentName);
            $templateProcessor->setValue('studentPlaceOfBirth' , $cityName );
            $templateProcessor->setValue('studentDateOfBirth' , $formattedDateOfBirth);
            $templateProcessor->setValue('studentNik' , $identityNo);
            $templateProcessor->setValue('studentGender' , $studentGender);
            $templateProcessor->setValue('studentEmployment' , $studentEmployment);
            $templateProcessor->setValue('studentEmail' , $student->student_email);
            $templateProcessor->setValue('studentPhoneNo' , $student->student_phone_no);
            $templateProcessor->setValue('currentDate' , $currentDate);

            ob_start();

            $templateProcessor->saveAs("php://output");

            $contents = ob_get_contents();

            ob_end_clean();

            return response()->streamDownload(function () use ($contents) {
                echo $contents;
            }, str_replace(['/', ' '], '_', $studentName . '-' . $contractNo) . '.docx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
                'Content-Disposition' => 'attachment; filename="'.str_replace(['/', ' '], '_', $studentName . '-' . $contractNo. '-Document-New-Member-Registration').'.docx"',
            ]);
        }
    }

    public function documentHirePurchaseApplication(ContractSchema $schema, Contract $contract) {

        if (!$contract) {
            return response()->json(['error' => 'Contract not found'], 404);
        }

        if ($contract) {
            $student = $contract->contractStudent;
            $studentName = $student->student_name;
            $contractNo = $contract->contract_no;
            $studentEmployment = $student->lpkCategory->category->category_name ?? 'N/A';
            Carbon::setLocale('id');
            $currentDate = Carbon::now()->translatedFormat('d F Y');;
            $lpk = $contract->contractLpk;
            $templateProcessor = new TemplateProcessor(base_path('resources/lpk/doc/template_hire_purchase_application.docx'));

            // Replace placeholders with actual values
            $templateProcessor->setValue('studentName', $studentName);
            $templateProcessor->setValue('studentEmployment' , $studentEmployment);
            $templateProcessor->setValue('studentAddressInIndonesia' , $student->student_address);
            $templateProcessor->setValue('studentAddressInJapan' , $student->student_address_in_japan);
            $templateProcessor->setValue('studentPhoneNo' , $student->student_phone_no);
            $templateProcessor->setValue('currentDate' , $currentDate);
            $templateProcessor->setValue('lpkName' , $lpk->lpk_name);

            ob_start();

            $templateProcessor->saveAs("php://output");

            $contents = ob_get_contents();

            ob_end_clean();

            return response()->streamDownload(function () use ($contents) {
                echo $contents;
            }, str_replace(['/', ' '], '_', $studentName . '-' . $contractNo) . '.docx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
                'Content-Disposition' => 'attachment; filename="'.str_replace(['/', ' '], '_', $studentName . '-' . $contractNo. '-Document-Hire-Purchase-Application').'.docx"',
            ]);
        }
    }

    public function documentStatementOfWillingnessToPay(ContractSchema $schema, Contract $contract) {

        if (!$contract) {
            return response()->json(['error' => 'Contract not found'], 404);
        }

        if ($contract) {
            $student = $contract->contractStudent;
            $studentName = $student->student_name;
            $contractNo = $contract->contract_no;
            $indonesia_currency = NumberFormatter::create('id', NumberFormatter::CURRENCY);
            $formattedAmount = $indonesia_currency->format($contract->contract_principle);
            $formattedAmount = rtrim(rtrim($formattedAmount, '0'), ',');
            $interest = rtrim(rtrim(number_format($contract->contractPaymentPlan->payment_plan_interest, 2), '0'), '.') . '%';
            $paymentPlanRange = $contract->contractPaymentPlan->payment_plan_range;
            $studentEmployment = $student->lpkCategory->category->category_name ?? 'N/A';
            Carbon::setLocale('id');
            $currentDate = Carbon::now()->translatedFormat('d F Y');;

            $templateProcessor = new TemplateProcessor(base_path('resources/lpk/doc/template_statement_of_willingness_to_pay.docx'));

            // Replace placeholders with actual values
            $templateProcessor->setValue('studentName', $studentName);
            $templateProcessor->setValue('studentEmployment' , $studentEmployment);
            $templateProcessor->setValue('paymentPlanRange' , $paymentPlanRange);
            $templateProcessor->setValue('studentAddressInIndonesia' , $student->student_address);
            $templateProcessor->setValue('studentAddressInJapan' , $student->student_address_in_japan);
            $templateProcessor->setValue('studentPhoneNo' , $student->student_phone_no);
            $templateProcessor->setValue('billAmount' , str_replace("X", "", 'Rp. ' .$formattedAmount));
            $templateProcessor->setValue('bunga' , $interest);
            $templateProcessor->setValue('dueDate' , $contract->contract_payday);
            $templateProcessor->setValue('currentDate' , $currentDate);
            ob_start();

            $templateProcessor->saveAs("php://output");

            $contents = ob_get_contents();

            ob_end_clean();

            return response()->streamDownload(function () use ($contents) {
                echo $contents;
            }, str_replace(['/', ' '], '_', $studentName . '-' . $contractNo) . '.docx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
                'Content-Disposition' => 'attachment; filename="'.str_replace(['/', ' '], '_', $studentName . '-' . $contractNo. '-Document-Statement-Of-Willingness-To-Pay').'.docx"',
            ]);
        }
    }

    public function documentStatementandGuaranteeLatter(ContractSchema $schema, Contract $contract) {

        if (!$contract) {
            return response()->json(['error' => 'Contract not found'], 404);
        }

        if ($contract) {
            $student = $contract->contractStudent;
            $studentName = $student->student_name;
            $contractNo = $contract->contract_no;
            $studentEmployment = $student->lpkCategory->category->category_name ?? 'N/A';
            Carbon::setLocale('id');
            $identityType1 = $student->studentIdentitys->firstWhere('student_identity_type', 1);
            $identityNo = $identityType1 ? $identityType1->student_identity_no : 'N/A';
            $studentDateOfBirth = Carbon::parse($student->student_date_of_birth);
            $formattedDateOfBirth = $studentDateOfBirth->translatedFormat('d F Y');
            $lpk = $contract->contractLpk;
            $cityName = $lpk->lpkContactPersonPlaceOfBirth ? $lpk->lpkContactPersonPlaceOfBirth->city_name : 'N/A';

            $templateProcessor = new TemplateProcessor(base_path('resources/lpk/doc/template_statement_and_guarantee_letter.docx'));

            // Replace placeholders with actual values
            $templateProcessor->setValue('personName', $lpk->lpk_contact_person_name);
            $templateProcessor->setValue('personPlaceOfBirth', $cityName);
            $templateProcessor->setValue('personDateOfBirth', $lpk->lpk_contact_person_date_of_birth);
            $templateProcessor->setValue('personNationality', $lpk->lpk_contact_person_nationality);
            $templateProcessor->setValue('personIdentityNo', $lpk->lpk_contact_person_identity_no);
            $templateProcessor->setValue('personPassportNo', $lpk->lpk_contact_person_passport_no);
            $templateProcessor->setValue('personOccupation', $lpk->lpk_contact_person_occupation);
            $templateProcessor->setValue('personAddress', $lpk->lpk_contact_person_address);



            $templateProcessor->setValue('studentName', $studentName);
            $templateProcessor->setValue('studPlaceOfBirth' , $cityName );
            $templateProcessor->setValue('studDateOfBirth' , $formattedDateOfBirth);
            $templateProcessor->setValue('studentNationality' , 'Indonesia');
            $templateProcessor->setValue('studentNik' , $identityNo);
            $templateProcessor->setValue('studentParentName' , $student->student_parent_name);
            $templateProcessor->setValue('studentEmployment' , $studentEmployment);
            $templateProcessor->setValue('studentAddressInJapan' , $student->student_address_in_japan);


            ob_start();

            $templateProcessor->saveAs("php://output");

            $contents = ob_get_contents();

            ob_end_clean();

            return response()->streamDownload(function () use ($contents) {
                echo $contents;
            }, str_replace(['/', ' '], '_', $studentName . '-' . $contractNo) . '.docx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
                'Content-Disposition' => 'attachment; filename="'.str_replace(['/', ' '], '_', $studentName . '-' . $contractNo. '-Document-Statement-and-Guarantee-Latter').'.docx"',
            ]);
        }
    }

    public function documentDiplomaSubmissionReceipt(ContractSchema $schema, Contract $contract) {

        if (!$contract) {
            return response()->json(['error' => 'Contract not found'], 404);
        }

        if ($contract) {
            $student = $contract->contractStudent;
            $studentName = $student->student_name;
            $contractNo = $contract->contract_no;
            $identityType1 = $student->studentIdentitys->firstWhere('student_identity_type', 1);
            $identityNo = $identityType1 ? $identityType1->student_identity_no : 'N/A';
            $identityType5 = $student->studentIdentitys->firstWhere('student_identity_type', 5);
            $identityIjazahNo = $identityType5 ? $identityType5->student_identity_no : 'N/A';
            $identityEducationInstitutName = $identityType5 ? $identityType5->student_identity_name_of_educational_institution : 'N/A';
            $lpk = $contract->contractLpk;
            Carbon::setLocale('id');
            $currentDate = Carbon::now()->translatedFormat('d F Y');;

            $templateProcessor = new TemplateProcessor(base_path('resources/lpk/doc/template_diploma_submission_receipt.docx'));

            // Replace placeholders with actual values
            $templateProcessor->setValue('studentName', $studentName);
            $templateProcessor->setValue('studentNik', $identityNo);
            $templateProcessor->setValue('studentAddress' , $student->student_address);
            $templateProcessor->setValue('studentIjazahNo' , $identityIjazahNo);
            $templateProcessor->setValue('studentNameEducationInstitut' , $identityEducationInstitutName);
            $templateProcessor->setValue('currentDate' , $currentDate);
            $templateProcessor->setValue('lpkName' , $lpk->lpk_name);


            ob_start();

            $templateProcessor->saveAs("php://output");

            $contents = ob_get_contents();

            ob_end_clean();

            return response()->streamDownload(function () use ($contents) {
                echo $contents;
            }, str_replace(['/', ' '], '_', $studentName . '-' . $contractNo) . '.docx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
                'Content-Disposition' => 'attachment; filename="'.str_replace(['/', ' '], '_', $studentName . '-' . $contractNo. '-Document-Diploma-Submission-Receipt').'.docx"',
            ]);
        }
    }


    public function store(ContractSchema $schema, ContractRequest $request, ContractQuery $query)
    {
        $student = Student::find($request->data['attributes']['contractStudent']);

        $contract = Contract::create([
            'lpk_id' => $student->lpk_id,
            'student_id' => $request->data['attributes']['contractStudent'],
            'contract_payment_plan_id' => $request->data['attributes']['contractPaymentPlan'],
            'contract_date_of_departure' => $request->data['attributes']['contractDateOfDeparture'],
            'contract_initial_contract_in_japan' => $request->data['attributes']['contractInitialContractInJapan'],
            'contract_end_of_contract_in_japan' => $request->data['attributes']['contractEndOfContractInJapan'],
            'contract_payday' => $request->data['attributes']['contractPayday'],
            'contract_process_fee' => $request->data['attributes']['contractProcessFee'],
            'contract_membership' => $request->data['attributes']['contractMembership'] ?? null,
            'contract_provision_fee_enabled' => $request->data['attributes']['contractProvisionFeeEnabled'] ?? false,
            'contract_tax_enabled' => $request->data['attributes']['contractTaxEnabled'] ?? true,
            'contract_tax_rate' => $request->data['attributes']['contractTaxRate'] ?? 0.5,
            'contract_principle' => $request->data['attributes']['contractPrinciple'],
            'contract_total_payable' => $request->data['attributes']['contractTotalPayable'],
            'contract_first_working_day' => $request->data['attributes']['contractFirstWorkingDay'],
            // 'contract_start_date' => $request->data['attributes']['contractStartDate'],
            'contract_grace_period' => $request->data['attributes']['contractGracePeriod'],
            'contract_disbursment_date' => $request->data['attributes']['contractDisbursmentDate'],
            'contract_wage_daily' => $request->data['attributes']['contractWageDaily'],
            'contract_days_off' => $request->data['attributes']['contractDaysOff'],
            'contract_cut_off_date' => $request->data['attributes']['contractCutOffDate'],
            'contract_kurs_yen' => $request->data['attributes']['contractKursYen'],
            'contract_provision_already_paid' => $request->data['attributes']['contractProvisionAlreadyPaid'],
            'contract_student_need_training' => $request->data['attributes']['contractStudentNeedTraining'],
            'contract_remark' => $request->data['attributes']['contractRemark'],
            'contract_timezone' => Auth::user()->timezone,
            'status'=> 1,
        ]);



        if (isset($request->data['attributes']["contractAttachmentFormulirPendaftaranAnggotaBaru"])) {
            self::handleFileUpload($contract, 'contractAttachmentFormulirPendaftaranAnggotaBaru', $request->data['attributes']["contractAttachmentFormulirPendaftaranAnggotaBaru"]);
        }

        if (isset($request->data['attributes']["contractAttachmentSuratPermohonanSewaBeli"])) {
            self::handleFileUpload($contract, 'contractAttachmentSuratPermohonanSewaBeli', $request->data['attributes']["contractAttachmentSuratPermohonanSewaBeli"]);
        }

        if (isset($request->data['attributes']["contractAttachmentSuratPernyataanKesanggupanMembayar"])) {
            self::handleFileUpload($contract, 'contractAttachmentSuratPernyataanKesanggupanMembayar', $request->data['attributes']["contractAttachmentSuratPernyataanKesanggupanMembayar"]);
        }


        if (isset($request->data['attributes']["contractAttachmentPerjanjianSewaBeli"])) {
            self::handleFileUpload($contract, 'contractAttachmentPerjanjianSewaBeli', $request->data['attributes']["contractAttachmentPerjanjianSewaBeli"]);
        }


        if (isset($request->data['attributes']["contractAttachmentSuratPernyataanDanJaminan"])) {
            self::handleFileUpload($contract, 'contractAttachmentSuratPernyataanDanJaminan', $request->data['attributes']["contractAttachmentSuratPernyataanDanJaminan"]);
        }


        // if (isset($request->data['attributes']["contractAttachmentSuratPernyataanPersetujuaanOrangTuaWali"])) {
        //     self::handleFileUpload($contract, 'contractAttachmentSuratPernyataanPersetujuaanOrangTuaWali', $request->data['attributes']["contractAttachmentSuratPernyataanPersetujuaanOrangTuaWali"]);
        // }

        if (isset($request->data['attributes']["contractAttachmentTandaTerimaPenyerahanIjasah"])) {
            self::handleFileUpload($contract, 'contractAttachmentTandaTerimaPenyerahanIjasah', $request->data['attributes']["contractAttachmentTandaTerimaPenyerahanIjasah"]);
        }



        //status_action = 1 (contract assign)
        // $studentStatus = new StudentStatus;
        // $studentStatus->student_id = $request->data['attributes']['contractStudent'];
        // $studentStatus->status = 2;
        // $studentStatus->status_ref_id =  $contract->id;
        // $studentStatus->status_action = 2;
        // $studentStatus->status_by_id = Auth::id();
        // $studentStatus->save();


            $users = User::role('admin', 'backoffice')->get();

            if ($users) {
                foreach ($users as $userEach) {
                    $userEach->notify(new ContractInfoNotification($contract));
                }
            }

        return new DataResponse($contract);
    }


    /**
     * Create a new resource.
     *
     * @param ContractSchema $schema
     * @param ContractRequest $request
     * @param ContractQuery $query
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */
    public function Update(ContractRequest $request, ContractSchema $schema, Contract $contract)
    {
        // $contractRequest = new ContractRequest;
        // Log::info($contractRequest->rules());
        // $validated = $request->validate([
        //     'contractStudent' => 'required|unique:posts|max:255'
        // ]);
        // Log::info("dasdasd");

        //contract can only updating dokument
        // $contract = Contract::where('id', $request->data['id'])->first();


        // if ($contract) {
        //     //check if current student is diffrent
        //     if($contract->contractStudent != $request->data['attributes']['contractStudent']){
        //         // check status student
        //         $newContractdStudent = Student::where('id', $request->data['attributes']['contractStudent'])->first();

        //         if($newContractdStudent->status == 2){
        //             return response()->json(['message' => 'Student sudah terdaftar dalam kontrak'], 422); //Only Test
        //             // return response()->json(['message' => 'Student sudah terdaftar dalam kontrak lain'], 422);
        //         }

        //         $oldContractdStudent = Student::where('id', $contract->contractStudent)->first();
        //         if($oldContractdStudent){
        //             $oldContractdStudent->status = 1;
        //             $oldContractdStudent->save();
        //         }

        //         if ($newContractdStudent) {
        //             $newContractdStudent->status = 2;
        //             $newContractdStudent->save();
        //         }
        //     }
        //     $contract->student_id= $request->data['attributes']['contractStudent'];
        // }


        // $contract->lpk_id= $request->data['attributes']['contractLpk'];
        // $contract->contract_payment_plan_id= $request->data['attributes']['contractPaymentPlan'];
        // $contract->contract_total_payable= $request->data['attributes']['contractTotalPayable'];

        // $contract->save();

        $student = Student::find($request->data['attributes']['contractStudent']);


        $contract->lpk_id = $student->lpk_id;
        $contract->student_id = $request->data['attributes']['contractStudent'];
        $contract->contract_payment_plan_id = $request->data['attributes']['contractPaymentPlan'];
        $contract->contract_date_of_departure = $request->data['attributes']['contractDateOfDeparture'];
        $contract->contract_initial_contract_in_japan = $request->data['attributes']['contractInitialContractInJapan'];
        $contract->contract_end_of_contract_in_japan = $request->data['attributes']['contractEndOfContractInJapan'];
        $contract->contract_payday = $request->data['attributes']['contractPayday'];
        $contract->contract_process_fee = $request->data['attributes']['contractProcessFee'];
        $contract->contract_membership = $request->data['attributes']['contractMembership'] ?? null;
        $contract->contract_provision_fee_enabled = $request->data['attributes']['contractProvisionFeeEnabled'] ?? false;
        $contract->contract_principle = $request->data['attributes']['contractPrinciple'];
        $contract->contract_total_payable = $request->data['attributes']['contractTotalPayable'];
        $contract->contract_first_working_day = $request->data['attributes']['contractFirstWorkingDay'];
        // $contract->contract_start_date = $request->data['attributes']['contractStartDate'];
        $contract->contract_disbursment_date = $request->data['attributes']['contractDisbursmentDate'];
        $contract->contract_wage_daily = $request->data['attributes']['contractWageDaily'];
        $contract->contract_cut_off_date = $request->data['attributes']['contractCutOffDate'];
        $contract->contract_days_off =$request->data['attributes']['contractDaysOff'];
        $contract->contract_kurs_yen = $request->data['attributes']['contractKursYen'];
        $contract->contract_provision_already_paid = $request->data['attributes']['contractProvisionAlreadyPaid'];
        $contract->contract_student_need_training = $request->data['attributes']['contractStudentNeedTraining'];
        $contract->contract_remark = $request->data['attributes']['contractRemark'];
        $contract->contract_grace_period = $request->data['attributes']['contractGracePeriod'];
        $contract->contract_timezone = Auth::user()->timezone;

        if (isset($request->data['attributes']['contractTaxEnabled'])) {
            $contract->contract_tax_enabled = $request->data['attributes']['contractTaxEnabled'];
        }
        if (isset($request->data['attributes']['contractTaxRate'])) {
            $contract->contract_tax_rate = $request->data['attributes']['contractTaxRate'];
        }

        $contract->save();



        if (isset($request->data['attributes']["contractAttachmentFormulirPendaftaranAnggotaBaru"])) {
            self::handleFileUpload($contract, 'contractAttachmentFormulirPendaftaranAnggotaBaru', $request->data['attributes']["contractAttachmentFormulirPendaftaranAnggotaBaru"]);
        }

        if (isset($request->data['attributes']["contractAttachmentSuratPermohonanSewaBeli"])) {
            self::handleFileUpload($contract, 'contractAttachmentSuratPermohonanSewaBeli', $request->data['attributes']["contractAttachmentSuratPermohonanSewaBeli"]);
        }

        if (isset($request->data['attributes']["contractAttachmentSuratPernyataanKesanggupanMembayar"])) {
            self::handleFileUpload($contract, 'contractAttachmentSuratPernyataanKesanggupanMembayar', $request->data['attributes']["contractAttachmentSuratPernyataanKesanggupanMembayar"]);
        }


        if (isset($request->data['attributes']["contractAttachmentPerjanjianSewaBeli"])) {
            self::handleFileUpload($contract, 'contractAttachmentPerjanjianSewaBeli', $request->data['attributes']["contractAttachmentPerjanjianSewaBeli"]);
        }


        if (isset($request->data['attributes']["contractAttachmentSuratPernyataanDanJaminan"])) {
            self::handleFileUpload($contract, 'contractAttachmentSuratPernyataanDanJaminan', $request->data['attributes']["contractAttachmentSuratPernyataanDanJaminan"]);
        }


        // if (isset($request->data['attributes']["contractAttachmentSuratPernyataanPersetujuaanOrangTuaWali"])) {
        //     self::handleFileUpload($contract, 'contractAttachmentSuratPernyataanPersetujuaanOrangTuaWali', $request->data['attributes']["contractAttachmentSuratPernyataanPersetujuaanOrangTuaWali"]);
        // }

        if (isset($request->data['attributes']["contractAttachmentTandaTerimaPenyerahanIjasah"])) {
            self::handleFileUpload($contract, 'contractAttachmentTandaTerimaPenyerahanIjasah', $request->data['attributes']["contractAttachmentTandaTerimaPenyerahanIjasah"]);
        }

            $users = User::role('admin', 'backoffice')->get();

            if ($users) {
                foreach ($users as $userEach) {
                    $userEach->notify(new ContractInfoNotification($contract));
                }
            }

        return new DataResponse($contract);
    }


      /**
     * History status for a given id.
     */
    public function historyStatus(ContractSchema $schema, Contract $contract)

    {
        return new DataResponse($contract->historyStatus);
    }


      /**
     * History payment for a given id.
     */
    public function historyPayment(ContractSchema $schema, Contract $contract)

    {
        return new DataResponse($contract->historyPayment);
    }


      /**
     * Installment for a given id.
     */
    public function installment(ContractSchema $schema, Contract $contract)

    {
        return new DataResponse($contract->contractInstallment);
    }


      /**
     * active Installment for a given id.
     */
    public function installmentActive(ContractSchema $schema, Contract $contract)

    {
        return new DataResponse($contract->installmentActive);
    }

    /**
     * Update status for a given id.
     */
    public function updateStatus(ContractSchema $schema, ContractUpdateStatusRequest $request, Contract $contract)
    {

        //   status==1 Active
        //   status==2 Complete

        $contractStatus = new ContractStatus;
        $contractStatus->contract_id = $contract->id;
        $contractStatus->status = $request->data['attributes']['status'];
        $contractStatus->status_action = $request->data['attributes']['action'];
        $contractStatus->status_by_id = auth()->user()->id;
        $contractStatus->save();

        $contract->status = $request->data['attributes']['status'];

        return new DataResponse($contract);
    }

    public function uploadDoc(ContractSchema $schema, ContractUploadDocRequest $request, Contract $contract)
    {

        if (isset($request->data['attributes']["contractAttachmentFormulirPendaftaranAnggotaBaru"])) {
            self::handleFileUpload($contract, 'contractAttachmentFormulirPendaftaranAnggotaBaru', $request->data['attributes']["contractAttachmentFormulirPendaftaranAnggotaBaru"]);
        }

        if (isset($request->data['attributes']["contractAttachmentSuratPermohonanSewaBeli"])) {
            self::handleFileUpload($contract, 'contractAttachmentSuratPermohonanSewaBeli', $request->data['attributes']["contractAttachmentSuratPermohonanSewaBeli"]);
        }

        if (isset($request->data['attributes']["contractAttachmentSuratPernyataanKesanggupanMembayar"])) {
            self::handleFileUpload($contract, 'contractAttachmentSuratPernyataanKesanggupanMembayar', $request->data['attributes']["contractAttachmentSuratPernyataanKesanggupanMembayar"]);
        }


        if (isset($request->data['attributes']["contractAttachmentPerjanjianSewaBeli"])) {
            self::handleFileUpload($contract, 'contractAttachmentPerjanjianSewaBeli', $request->data['attributes']["contractAttachmentPerjanjianSewaBeli"]);
        }


        if (isset($request->data['attributes']["contractAttachmentSuratPernyataanDanJaminan"])) {
            self::handleFileUpload($contract, 'contractAttachmentSuratPernyataanDanJaminan', $request->data['attributes']["contractAttachmentSuratPernyataanDanJaminan"]);
        }


        if (isset($request->data['attributes']["contractAttachmentTandaTerimaPenyerahanIjasah"])) {
            self::handleFileUpload($contract, 'contractAttachmentTandaTerimaPenyerahanIjasah', $request->data['attributes']["contractAttachmentTandaTerimaPenyerahanIjasah"]);
        }

        return new DataResponse($contract);
    }



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
                case 'image/webp':
                case 'webp':
                    return '.webp';
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
            'JVBERi0'     => ".pdf",
            'R0lGODdh'    => ".gif",
            'R0lGODlh'    => ".gif",
            'iVBORw0KGgo' => ".png",
            '/9j/'        => ".jpeg",
            'UklGR'       => ".webp",

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
            $model->addMediaFromBase64($imageBase64, ['image/webp', 'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'application/pdf'])->usingFileName($filename)->toMediaCollection($collection);

            Log::info($filename);
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




}
