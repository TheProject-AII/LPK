<?php

namespace App\Http\Controllers\Api\BackOffice\V1;

use App\Http\Controllers\Controller;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use App\JsonApi\BackOffice\V1\Students\StudentQuery;
use App\JsonApi\BackOffice\V1\Students\StudentRequest;
use App\JsonApi\BackOffice\V1\Students\StudentUpdateStatusRequest;
use App\JsonApi\BackOffice\V1\Students\StudentResetPasswordRequest;
use App\JsonApi\BackOffice\V1\Students\StudentSchema;
use App\Models\Student;
use App\Models\User;
use App\Models\StudentIdentity;
use App\Models\StudentLicense;
use App\Models\StudentBankAccount;
use App\Models\StudentStatus;
use Carbon\Carbon;
use LaravelJsonApi\Core\Responses\DataResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Hash; // Tambahkan baris ini
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Notifications\StatusStudentUpdateWaitingApprovalNotification;

class StudentController extends Controller
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
     * @param StudentSchema $schema
     * @param StudentRequest $request
     * @param StudentQuery $query
     * @param  string  $mediaCollectionName
     * @return \Illuminate\Contracts\Support\Responsable|\Illuminate\Http\Response
     */

     public function downloadDocSuratPersetujuanOrangTua(StudentSchema $schema, Student $student) {

        if (!$student) {
            return response()->json(['error' => 'Contract not found'], 404);
        }

        if ($student) {
            $studentName = $student->student_name;
            // $studentNo = $student->student_no;
            // $studentEmployment = $student->lpkCategory->category->category_name ?? 'N/A';
            Carbon::setLocale('id');

            // Get identity type and student birth date
            $identityType1 = $student->studentIdentitys->firstWhere('student_identity_type', 1);
            $identityNo = $identityType1 ? $identityType1->student_identity_no : 'N/A';
            $studentDateOfBirth = Carbon::parse($student->student_date_of_birth);
            $formattedDateOfBirth = $studentDateOfBirth->translatedFormat('d F Y');
            $studentEmployment = $student->lpkCategory->category->category_name ?? 'N/A';

            // Initialize TemplateProcessor
            $templateProcessor = new TemplateProcessor(base_path('resources/lpk/doc/template_parental_approval_letter.docx'));


            // Get current date in Indonesian format
            $currentDate = Carbon::now();
            $day = $currentDate->day;
            $month = $currentDate->translatedFormat('F');
            $year = $currentDate->year;

            // Replace placeholders with actual values
            $templateProcessor->setValue('lpkName', $student->studentLpk->lpk_name);
            $templateProcessor->setValue('studentName', $studentName);
            // template_parental_approval_letter.docx
            // $templateProcessor->setValue('studPlaceOfBirth' , $cityName );
            // $templateProcessor->setValue('studDateOfBirth' , $formattedDateOfBirth);
            $templateProcessor->setValue('day', $day);
            $templateProcessor->setValue('month', $month);
            $templateProcessor->setValue('year', $year);
            $templateProcessor->setValue('studentAddress', $student->student_address);
            $templateProcessor->setValue('studentParentName', $student->student_parent_name);
            $templateProcessor->setValue('studentEmployment', $studentEmployment);

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

     public function downloadRequiredDocument(StudentSchema $schema, Student $student, $mediaCollectionName)
    {
        Log::info($mediaCollectionName);
        // Log::info($studentId);
        // Validate the media collection name
        // $validCollections = [
        //     'ktpPemohonPenjamin',
        //     'kartuKeluarga',
        //     'ijazahSekolah',
        //     'fotoKeluarga',
        //     'kontrakPerjanjianHirePurchase',
        //     'suratPermohonanHirePurchase',
        //     'suratKesanggupanBayar',
        //     'hasilPraMedicalCheckUp',
        //     'nameTagLpk',
        //     'certificateOfEligibility',
        //     'surpervisingOrganization',
        //     'kontrakKerjaDiJepang',
        //     'tiketPesawatKeberangkatan',
        //     'formulirPendaftaranKoperasi',
        //     'suratPersetujuanOrangTua',
        // ];

        $validCollectionsKebabCase = [
            'ktp-pemohon-penjamin',
            'kartu-keluarga',
            'ijazah-sekolah',
            'foto-keluarga',
            'kontrak-perjanjian-hire-purchase',
            'surat-permohonan-hire-purchase',
            'surat-kesanggupan-bayar',
            'hasil-pra-medical-check-up',
            'name-tag-lpk',
            'certificate-of-eligibility',
            'surpervising-organization',
            'kontrak-kerja-di-jepang',
            'tiket-pesawat-keberangkatan',
            'formulir-pendaftaran-koperasi',
            'surat-persetujuan-orang-tua',
        ];

        if (!in_array($mediaCollectionName, $validCollectionsKebabCase)) {
            abort(404, 'Media collection not found.');
        }

        $mediaCollectionNameCamelCase = lcfirst(str_replace('-', '', ucwords($mediaCollectionName, '-')));

        // Fetch media items from the specified collection
        $mediaItems = $student->getMedia($mediaCollectionNameCamelCase);

        if (count($mediaItems) === 0) {
            abort(404, 'No media found.');
        }

        // Get the first media item (assuming single file in collection)
        $media = $mediaItems[0];

        // Stream the file for download
        // return new StreamedResponse(function () use ($media) {
        //     $path = $media->getPath();
        //     $file = fopen($path, 'r');
        //     while (!feof($file)) {
        //         echo fread($file, 8192);
        //         flush();
        //     }
        //     fclose($file);
        // }, 200, [
        //     'Content-Type' => $media->mime_type,
        //     'Content-Disposition' => 'attachment; filename="' . $media->file_name . '"',
        // ]);

        // return $media->toResponse($request);
        // return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;

        // Set header to force download
        return response()->streamDownload(function () use ($media) {
            echo file_get_contents($media->getPath());
        }, $media->file_name, [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $media->file_name . '"',
        ]);
    }

    public function store(StudentSchema $schema, StudentRequest $request, StudentQuery $query)
    {

        // Log::info($request->data);
        if(auth()->user()->lpk_id && auth()->user()->lpk_id != null){
            $lpk_id =  auth()->user()->lpk_id;
         }
         else{
            $lpk_id = $request->data['attributes']['studentLpk'];
         }

        $student = new Student;
        // $student->student_no = 2;
        $student->student_name = $request->data['attributes']['studentName'];
        $student->student_type = $request->data['attributes']['studentType'];
        $student->student_address = $request->data['attributes']['studentAddress']?? null;
        $student->student_parent_name = $request->data['attributes']['studentParentName']?? null;
        $student->student_parent_relationship = $request->data['attributes']['studentParentRelationship']?? null;
        $student->student_parent_phone_no = $request->data['attributes']['studentParentPhoneNo']?? null;
        $student->student_email = $request->data['attributes']['studentEmail'];
        $student->student_place_of_birth_id = $request->data['attributes']['studentPlaceOfBirth']?? null;
        $student->student_date_of_birth = $request->data['attributes']['studentDateOfBirth']?? null;
        $student->student_gender = $request->data['attributes']['studentGender']?? null;
        $student->student_blood_type = $request->data['attributes']['studentBloodType']?? null;
        $student->student_religion = $request->data['attributes']['studentReligion']?? null;
        $student->student_last_education = $request->data['attributes']['studentLastEducation']?? null;
        $student->student_phone_no = $request->data['attributes']['studentPhoneNo']?? null;
        $student->student_kumiai_name = $request->data['attributes']['studentKumiaiName']?? null;
        $student->student_kumiai_address = $request->data['attributes']['studentKumiaiAddress']?? null;
        $student->student_employement_in_japan = $request->data['attributes']['studentEmployementInJapan']?? null;
        $student->student_address_in_japan = $request->data['attributes']['studentAddressInJapan']?? null;
        $student->student_emergency_phone_no = $request->data['attributes']['studentEmergencyPhoneNo'] ?? null;
        $student->student_work_program_in_japan = $request->data['attributes']['studentWorkProgramInJapan'] ?? null;
        $student->student_company_name_in_japan = $request->data['attributes']['studentCompanyNameInJapan'] ?? null;
        $student->password = Hash::make($request->data['attributes']['password']);
        $student->status = 1; //status register
        $student->lpk_id = $lpk_id;

        if (isset($request->data['attributes']["studentPhoto"])) {
            self::handleFileUpload($student, 'studentAttachmentPhoto', $request->data['attributes']["studentPhoto"]);
        }

        if (isset($request->data['attributes']["requiredDocktpPemohonPenjamin"])) {
            self::handleFileUpload($student, 'ktpPemohonPenjamin', $request->data['attributes']["requiredDocktpPemohonPenjamin"]);
        }
        if (isset($request->data['attributes']["requiredDockartuKeluarga"])) {
            self::handleFileUpload($student, 'kartuKeluarga', $request->data['attributes']["requiredDockartuKeluarga"]);
        }
        if (isset($request->data['attributes']["requiredDocijazahSekolah"])) {
            self::handleFileUpload($student, 'ijazahSekolah', $request->data['attributes']["requiredDocijazahSekolah"]);
        }
        if (isset($request->data['attributes']["requiredDocfotoKeluarga"])) {
            self::handleFileUpload($student, 'fotoKeluarga', $request->data['attributes']["requiredDocfotoKeluarga"]);
        }
        if (isset($request->data['attributes']["requiredDockontrakPerjanjianHirePurchase"])) {
            self::handleFileUpload($student, 'kontrakPerjanjianHirePurchase', $request->data['attributes']["requiredDockontrakPerjanjianHirePurchase"]);
        }
        if (isset($request->data['attributes']["requiredDocsuratPermohonanHirePurchase"])) {
            self::handleFileUpload($student, 'suratPermohonanHirePurchase', $request->data['attributes']["requiredDocsuratPermohonanHirePurchase"]);
        }
        if (isset($request->data['attributes']["requiredDocsuratKesanggupanBayar"])) {
            self::handleFileUpload($student, 'suratKesanggupanBayar', $request->data['attributes']["requiredDocsuratKesanggupanBayar"]);
        }
        if (isset($request->data['attributes']["requiredDochasilPraMedicalCheckUp"])) {
            self::handleFileUpload($student, 'hasilPraMedicalCheckUp', $request->data['attributes']["requiredDochasilPraMedicalCheckUp"]);
        }
        if (isset($request->data['attributes']["requiredDocnameTagLpk"])) {
            self::handleFileUpload($student, 'nameTagLpk', $request->data['attributes']["requiredDocnameTagLpk"]);
        }
        if (isset($request->data['attributes']["requiredDoccertificateOfEligibility"])) {
            self::handleFileUpload($student, 'certificateOfEligibility', $request->data['attributes']["requiredDoccertificateOfEligibility"]);
        }
        if (isset($request->data['attributes']["requiredDocsurpervisingOrganization"])) {
            self::handleFileUpload($student, 'surpervisingOrganization', $request->data['attributes']["requiredDocsurpervisingOrganization"]);
        }
        if (isset($request->data['attributes']["requiredDockontrakKerjaDiJepang"])) {
            self::handleFileUpload($student, 'kontrakKerjaDiJepang', $request->data['attributes']["requiredDockontrakKerjaDiJepang"]);
        }
        if (isset($request->data['attributes']["requiredDoctiketPesawatKeberangkatan"])) {
            self::handleFileUpload($student, 'tiketPesawatKeberangkatan', $request->data['attributes']["requiredDoctiketPesawatKeberangkatan"]);
        }
        if (isset($request->data['attributes']["requiredDocformulirPendaftaranKoperasi"])) {
            self::handleFileUpload($student, 'formulirPendaftaranKoperasi', $request->data['attributes']["requiredDocformulirPendaftaranKoperasi"]);
        }
        if (isset($request->data['attributes']["requiredDocsuratPersetujuanOrangTua"])) {
            self::handleFileUpload($student, 'suratPersetujuanOrangTua', $request->data['attributes']["requiredDocsuratPersetujuanOrangTua"]);
        }


        $student->save();

        foreach ($request->data['attributes']['studentIdentitys'] as $value) {
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
        }
        // foreach ($request->data['attributes']['studentLicenses'] as $value) {
        // $studentLicense = studentLicense::create([
        //     'student_id' => $student->id,
        //     'student_license_type' => $value["studentLicenseType"],
        //     'student_license_no' => $value["studentLicenseNo"],
        //     'student_license_expire_date' => $value["studentLicenseExpireDate"],
        //     'student_license_address' => $value["studentLicenseAddress"],
        //     'student_license_place_of_issue' => $value["studentLicensePlaceOfIssue"]
        // ]);
        //     $studentLicense = new StudentLicense;
        //     $studentLicense->student_id = $student->id;
        //     $studentLicense->student_license_type = $value["studentLicenseType"];
        //     $studentLicense->student_license_no = $value["studentLicenseNo"];
        //     $studentLicense->student_license_expire_date = $value["studentLicenseExpireDate"] ?? null;
        //     $studentLicense->student_license_address = $value["studentLicenseAddress"] ?? null;
        //     $studentLicense->student_license_place_of_issue = $value["studentLicensePlaceOfIssue"] ?? null;
        //     $studentLicense->save();
        //     if(isset($value["studentLicenseFile"])){
        //         self::handleFileUpload($studentLicense, 'student_attachment_license', $value["studentLicenseFile"] );
        //     }
        // }

        if (isset($request->data['attributes']['studentBankAccounts'])) {
            foreach ($request->data['attributes']['studentBankAccounts'] as $value) {
                $studentBankAccount = new StudentBankAccount;
                $studentBankAccount->student_id = $student->id;
                $studentBankAccount->student_bank_account_bank_name = $value["studentBankAccountBankName"];
                $studentBankAccount->student_bank_account_no = $value["studentBankAccountNo"];
                $studentBankAccount->student_bank_account_holder = $value["studentBankAccountHolder"] ?? null;
                $studentBankAccount->save();
            }
        }
        // return DataResponse::make( Student::find($student->id))->withServer('v1');
        return new DataResponse($student);
    }

    public function Update(StudentSchema $schema, StudentRequest $request, Student $student)
    {


        if(auth()->user()->lpk_id){
            $lpk_id =  auth()->user()->lpk_id;
         }
         else{
             $lpk_id = $request->data['attributes']['studentLpk'];
         }

        $student->student_name = $request->data['attributes']['studentName'];
        $student->student_type = $request->data['attributes']['studentType'];
        $student->student_address = $request->data['attributes']['studentAddress']?? null;
        $student->student_parent_name = $request->data['attributes']['studentParentName']?? null;
        $student->student_parent_relationship = $request->data['attributes']['studentParentRelationship']?? null;
        $student->student_parent_phone_no = $request->data['attributes']['studentParentPhoneNo'];
        $student->student_email = $request->data['attributes']['studentEmail'];
        $student->student_place_of_birth_id = $request->data['attributes']['studentPlaceOfBirth']?? null;
        $student->student_date_of_birth = $request->data['attributes']['studentDateOfBirth']?? null;
        $student->student_gender = $request->data['attributes']['studentGender']?? null;
        $student->student_blood_type = $request->data['attributes']['studentBloodType']?? null;
        $student->student_religion = $request->data['attributes']['studentReligion']?? null;
        $student->student_last_education = $request->data['attributes']['studentLastEducation']?? null;
        $student->student_phone_no = $request->data['attributes']['studentPhoneNo'];
        $student->student_kumiai_name = $request->data['attributes']['studentKumiaiName']?? null;
        $student->student_kumiai_address = $request->data['attributes']['studentKumiaiAddress']?? null;
        $student->student_employement_in_japan = $request->data['attributes']['studentEmployementInJapan']?? null;
        $student->student_work_program_in_japan = $request->data['attributes']['studentWorkProgramInJapan'] ?? null;
        $student->student_company_name_in_japan = $request->data['attributes']['studentCompanyNameInJapan'] ?? null;
        $student->student_address_in_japan = $request->data['attributes']['studentAddressInJapan']?? null;
        $student->student_emergency_phone_no = $request->data['attributes']['studentEmergencyPhoneNo'] ?? null;
        $student->lpk_id = $lpk_id;
        if(isset($request->data['attributes']['password'])){
          $student->password = Hash::make($request->data['attributes']['password']);
        }
        if (isset($request->data['attributes']["studentPhoto"])) {
            self::handleFileUpload($student, 'studentAttachmentPhoto', $request->data['attributes']["studentPhoto"]);
        }

        if (isset($request->data['attributes']["requiredDocktpPemohonPenjamin"])) {
            self::handleFileUpload($student, 'ktpPemohonPenjamin', $request->data['attributes']["requiredDocktpPemohonPenjamin"]);
        }
        if (isset($request->data['attributes']["requiredDockartuKeluarga"])) {
            self::handleFileUpload($student, 'kartuKeluarga', $request->data['attributes']["requiredDockartuKeluarga"]);
        }
        if (isset($request->data['attributes']["requiredDocijazahSekolah"])) {
            self::handleFileUpload($student, 'ijazahSekolah', $request->data['attributes']["requiredDocijazahSekolah"]);
        }
        if (isset($request->data['attributes']["requiredDocfotoKeluarga"])) {
            self::handleFileUpload($student, 'fotoKeluarga', $request->data['attributes']["requiredDocfotoKeluarga"]);
        }
        if (isset($request->data['attributes']["requiredDockontrakPerjanjianHirePurchase"])) {
            self::handleFileUpload($student, 'kontrakPerjanjianHirePurchase', $request->data['attributes']["requiredDockontrakPerjanjianHirePurchase"]);
        }
        if (isset($request->data['attributes']["requiredDocsuratPermohonanHirePurchase"])) {
            self::handleFileUpload($student, 'suratPermohonanHirePurchase', $request->data['attributes']["requiredDocsuratPermohonanHirePurchase"]);
        }
        if (isset($request->data['attributes']["requiredDocsuratKesanggupanBayar"])) {
            self::handleFileUpload($student, 'suratKesanggupanBayar', $request->data['attributes']["requiredDocsuratKesanggupanBayar"]);
        }
        if (isset($request->data['attributes']["requiredDochasilPraMedicalCheckUp"])) {
            self::handleFileUpload($student, 'hasilPraMedicalCheckUp', $request->data['attributes']["requiredDochasilPraMedicalCheckUp"]);
        }
        if (isset($request->data['attributes']["requiredDocnameTagLpk"])) {
            self::handleFileUpload($student, 'nameTagLpk', $request->data['attributes']["requiredDocnameTagLpk"]);
        }
        if (isset($request->data['attributes']["requiredDoccertificateOfEligibility"])) {
            self::handleFileUpload($student, 'certificateOfEligibility', $request->data['attributes']["requiredDoccertificateOfEligibility"]);
        }
        if (isset($request->data['attributes']["requiredDocsurpervisingOrganization"])) {
            self::handleFileUpload($student, 'surpervisingOrganization', $request->data['attributes']["requiredDocsurpervisingOrganization"]);
        }
        if (isset($request->data['attributes']["requiredDockontrakKerjaDiJepang"])) {
            self::handleFileUpload($student, 'kontrakKerjaDiJepang', $request->data['attributes']["requiredDockontrakKerjaDiJepang"]);
        }
        if (isset($request->data['attributes']["requiredDoctiketPesawatKeberangkatan"])) {
            self::handleFileUpload($student, 'tiketPesawatKeberangkatan', $request->data['attributes']["requiredDoctiketPesawatKeberangkatan"]);
        }
        if (isset($request->data['attributes']["requiredDocformulirPendaftaranKoperasi"])) {
            self::handleFileUpload($student, 'formulirPendaftaranKoperasi', $request->data['attributes']["requiredDocformulirPendaftaranKoperasi"]);
        }
        if (isset($request->data['attributes']["requiredDocsuratPersetujuanOrangTua"])) {
            self::handleFileUpload($student, 'suratPersetujuanOrangTua', $request->data['attributes']["requiredDocsuratPersetujuanOrangTua"]);
        }

        $student->save();

        $listIdIdentity = array();
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
        // $listIdLicense = array();
        // foreach ($request->data['attributes']['studentLicenses'] as $value) {
        //     if(isset($value["id"])){
        //         $studentLicense = StudentLicense::find($value["id"]);
        //         if($studentLicense && $studentLicense->exists()){
        //             $studentLicense->student_id = $student->id;
        //             $studentLicense->student_license_type = $value["studentLicenseType"];
        //             $studentLicense->student_license_no = $value["studentLicenseNo"];
        //             $studentLicense->student_license_expire_date = $value["studentLicenseExpireDate"] ?? null;
        //             $studentLicense->student_license_address = $value["studentLicenseAddress"] ?? null;
        //             $studentLicense->student_license_place_of_issue = $value["studentLicensePlaceOfIssue"] ?? null;
        //             $studentLicense->save();
        //             if(isset($value["studentLicenseFile"])){
        //                 self::handleFileUpload($studentLicense, 'student_attachment_license', $value["studentLicenseFile"] );
        //             }
        //             array_push($listIdLicense,$studentLicense->id);
        //         }
        //     }
        //     else{
        //         $studentLicense = new StudentLicense;
        //         $studentLicense->student_id = $student->id;
        //         $studentLicense->student_license_type = $value["studentLicenseType"];
        //         $studentLicense->student_license_no = $value["studentLicenseNo"];
        //         $studentLicense->student_license_expire_date = $value["studentLicenseExpireDate"] ?? null;
        //         $studentLicense->student_license_address = $value["studentLicenseAddress"] ?? null;
        //         $studentLicense->student_license_place_of_issue = $value["studentLicensePlaceOfIssue"] ?? null;
        //         $studentLicense->save();
        //         if(isset($value["studentLicenseFile"])){
        //             self::handleFileUpload($studentLicense, 'student_attachment_license', $value["studentLicenseFile"] );
        //         }
        //         array_push($listIdLicense,$studentLicense->id);
        //     }
        // }
        // $listIdLicenseNotfound = StudentLicense::whereNotIn('id', $listIdLicense)->get('id');
        // StudentLicense::destroy($listIdLicenseNotfound);
        $listIdBankAccount = array();
        if (isset($request->data['attributes']['studentBankAccounts'])) {
            foreach ($request->data['attributes']['studentBankAccounts'] as $value) {
                if (isset($value["id"])) {
                    $studentBankAccount = StudentBankAccount::find($value["id"]);
                    if ($studentBankAccount && $studentBankAccount->exists()) {
                        $studentBankAccount->student_id = $student->id;
                        $studentBankAccount->student_bank_account_bank_name = $value["studentBankAccountBankName"];
                        $studentBankAccount->student_bank_account_no = $value["studentBankAccountNo"];
                        $studentBankAccount->student_bank_account_holder = $value["studentBankAccountHolder"];
                        $studentBankAccount->save();
                        array_push($listIdBankAccount, $studentBankAccount->id);
                    }
                } else {
                    $studentBankAccount = new StudentBankAccount;
                    $studentBankAccount->student_id = $student->id;
                    $studentBankAccount->student_bank_account_bank_name = $value["studentBankAccountBankName"];
                    $studentBankAccount->student_bank_account_no = $value["studentBankAccountNo"];
                    $studentBankAccount->student_bank_account_holder = $value["studentBankAccountHolder"];
                    $studentBankAccount->save();
                    array_push($listIdBankAccount, $studentBankAccount->id);
                }
            }
        }
        if (empty($listIdBankAccount)) {
            StudentBankAccount::where('student_id', $student->id)->delete();
        } else {
            $listIdBankAccountNotfound = StudentBankAccount::where('student_id',$student->id)->whereNotIn('id', $listIdBankAccount)->get('id');
            StudentBankAccount::destroy($listIdBankAccountNotfound);
        }
        // return DataResponse::make( Student::find($student->id))->withServer('v1');
        return new DataResponse($student);
    }


      /**
     * History status for a given id.
     */
    public function historyStatus(StudentSchema $schema, Student $student)

    {
        return new DataResponse($student->historyStatus);
    }

      /**
     * History payment for a given id.
     */
    public function historyPayment(StudentSchema $schema, Student $student)

    {
        return new DataResponse($student->historyPayment);
    }

      /**
     * contract for a given id.
     */
    public function contract(StudentSchema $schema, Student $student)

    {
        // if($student->studentContractActive){
        //     return new DataResponse($student->studentContractActive);
        //   }
        //   else{
        //     return new DataResponse(null);
        //   }

        return new DataResponse($student->studentContractActive);
    }

      /**
     * Installment for a given id.
     */
    public function installment(StudentSchema $schema, Student $student)

    {
        if($student->studentContractActive){
          return new DataResponse($student->studentContractActive->contractInstallment);
        }
        else{
            return new DataResponse([]);
        }
    }

    /**
     * active Installment for a given id.
     */
    public function installmentActive(StudentSchema $schema, Student $student)

    {
        if($student->studentContractActive){
            return new DataResponse($student->studentContractActive->installmentActive);
          }
          else{
              return new DataResponse([]);
          }
    }

      /**
     * identity for a given id.
     */
    public function identity(StudentSchema $schema, Student $student)

    {
        return new DataResponse($student->studentIdentitys);
    }

      /**
     * bankAccount for a given id.
     */
    public function bankAccount(StudentSchema $schema, Student $student)

    {
        return new DataResponse($student->studentBankAccounts);
    }


    /**
     * Update status for a given id.
     */

    // public function updateStatus(StudentSchema $schema, StudentUpdateStatusRequest $request, Student $student)

    public function updateStatus(StudentSchema $schema, StudentUpdateStatusRequest $request, Student $student)

    {
        //status
        // 1 : registered
        // 2 : waiting approval
        // 3 : Rejected
        // 4 : waiting contract
        // 5 : Active
        // 6 : Complete

        $studentStatus = new StudentStatus;
        $studentStatus->student_id = $student->id;
        $studentStatus->status = $request->data['attributes']['status'];
        $studentStatus->status_action = $request->data['attributes']['action'];
        $studentStatus->status_remark = $request->data['attributes']['remarks'] ?? null;
        $studentStatus->status_by_id = auth()->user()->id;
        $studentStatus->save();

        $student->status = $request->data['attributes']['status'];

        // Send notification if the status is "2: waiting approval"
        if ($request->data['attributes']['status'] == 2) {
            $users = User::role('admin', 'backoffice')->get();

            if ($users) {
                foreach ($users as $userEach) {
                    $userEach->notify(new StatusStudentUpdateWaitingApprovalNotification($student->student_name,$student->studentLpk->lpk_name));
                }
            }
        }

        return new DataResponse($student);
    }

    public function resetPassword(StudentSchema $schema, StudentResetPasswordRequest $request , Student $student)

    {
        $student->password = Hash::make($request->data['attributes']['password']);
        $student->save();

        return new DataResponse($student);
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
            // 'JVBERi0'     => "application/pdf",
            // 'R0lGODdh'    => "image/gif",
            // 'R0lGODlh'    => "image/gif",
            // 'iVBORw0KGgo' => "image/png",
            // '/9j/'        => "image/jpeg"
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
