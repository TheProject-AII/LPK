<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;
use App\Http\Controllers\Api\BackOffice\V1\Auth\AuthController;
use App\Http\Controllers\Api\Student\V1\Auth\AuthStudentController;
use App\Http\Controllers\Api\Student\V1\Auth\AuthWebStudentController;
use App\Http\Controllers\Api\BackOffice\V1\Auth\Login2FAController;
use App\Http\Controllers\Api\BackOffice\V1\Auth\LoginGoogleAccountController;
use App\Http\Controllers\Api\BackOffice\V1\Auth\Verification2FAController;
use App\Http\Controllers\Api\BackOffice\V1\StudentController;
use App\Http\Controllers\Api\BackOffice\V1\ContractController;
use App\Http\Controllers\Api\BackOffice\V1\NotificationController;
use App\Http\Controllers\Api\BackOffice\V1\LpkController;
use App\Http\Controllers\Api\BackOffice\V1\RoleController;
use App\Http\Controllers\Api\BackOffice\V1\UserController;
use App\Http\Controllers\Api\BackOffice\V1\PaymentController;
use App\Http\Controllers\Api\Public\V1\LpkController as LpkPublicController;
use App\Http\Controllers\Api\BackOffice\V1\MapController;
use App\Http\Controllers\Api\BackOffice\V1\ReportSummaryController;
use App\Http\Controllers\Api\Student\V1\StudentUserController;
use App\Http\Controllers\Api\Student\V1\ConfigLpkUserController;
use App\Http\Controllers\Api\Duitku\V1\DuitlkuPOPController;
use Illuminate\Support\Facades\Log;
use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;
use App\Http\Middleware\AuthUsingApiKey;

/*
|-------------------------------------------------------------------------
| API Routes
|-------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|s
*/

Route::prefix('public/v1')->middleware(AuthUsingApiKey::class)->group(function () {
    // Route::get('/', function () {return json_encode(array('Public Access'));});
    Route::post('/lpks',[LpkPublicController::class,'store']);
});

Route::prefix('back-office/v1/auth')->group(function () {
    // Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login2FA', Login2FAController::class);
    Route::post('/login-google-account', LoginGoogleAccountController::class);
    Route::post('/verification-2fa', Verification2FAController::class);
});


Route::prefix('student/v1/auth')->group(function () {
    Route::post('/login', AuthStudentController::class);
    Route::post('/login-web', AuthWebStudentController::class);
    Route::get('/loginwebtest', function () {
        return 'Hello World';
    });

});

Route::prefix('duitku/v1')->group(function () {
    // Route::post('/inquiry', [DuitlkuPOPController::class,'inquiry']);
    Route::post('/callback', [DuitlkuPOPController::class,'callback']);
});

// Route::prefix('dashboard')->group(function() {
//     Route::get('summary/student',[ReportSummaryController::class, 'GetStudentStatus']);
//     Route::get('student/weekly',[ReportSummaryController::class, 'getRegisterWeekly']);
//     Route::get('student/yearly',[ReportSummaryController::class, 'getRegisterYearly']);
//     Route::get('payment/yearly',[ReportSummaryController::class, 'getRegisterYearly']);
// });

JsonApiRoute::server('backOffice-v1')->middleware('auth:backoffice')->prefix('back-office/v1')->resources(function ($server) {
    $server->resource('users', UserController::class)->only('index','show','store','update')->actions(function ($actions) {
        $actions->withId()->patch('reset-2fa');
        $actions->withId()->patch('update-status');
    });
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::patch('/user/update-profile', [UserController::class, 'UpdateProfile']);
    $server->resource('lpks', LpkController::class)->only('index','show','store','update')->actions(function ($actions) {
       $actions->withId()->patch('update-status');
    });

    $server->resource('students', StudentController::class)->only('index','show','store','update','delete')->actions(function ($actions) {
        $actions->withId()->patch('update-status');
        $actions->withId()->patch('reset-password');
        $actions->withId()->get('history-status');
        $actions->withId()->get('history-payment');
        $actions->withId()->get('contract');
        $actions->withId()->get('installment');
        $actions->withId()->get('installment-active');
        $actions->withId()->get('identity');
        $actions->withId()->get('bank-account');
        $actions->withId()->get('download-required-document/{mediaCollectionName}','downloadRequiredDocument');
        $actions->withId()->get('download-doc-surat-persetujuan-orang-tua');
    });

    // Route::get('/students/{studentId}/download/{mediaCollectionName}', [StudentController::class, 'download']);
    // $server->resource('contracts', ContractController::class)->only('index','show','store','update');
    $server->resource('contracts', ContractController::class)->only('index','show','store','update')->actions(function ($actions) {
        $actions->withId()->get('history-status');
        $actions->withId()->get('history-payment');
        $actions->withId()->get('installment');
        $actions->withId()->get('installment-active');
        $actions->withId()->get('document-contract');
        $actions->withId()->get('document-new-member-registration');
        $actions->withId()->get('document-hire-purchase-application');
        $actions->withId()->get('document-statement-of-willingness-to-pay');
        $actions->withId()->get('document-statement-and-guarantee-Latter');
        $actions->withId()->get('document-diploma-submission-receipt');
        $actions->withId()->patch('update-status');
        $actions->withId()->patch('upload-doc');
    });

    // Route::get('contracts/{id}/document-contract', [ContractController::class, 'documentContract']);
    // Route::get('contracts/{id}/document-contract', [ContractController::class, 'documentContract']);
    $server->resource('categories', JsonApiController::class)->only('index','show','store','update');
    $server->resource('cities', JsonApiController::class)->only('index','show','store','update');
    $server->resource('installments', JsonApiController::class)->only('index','show','store','update');
    $server->resource('payment-plans', JsonApiController::class)->only('index','show','store','update');
    $server->resource('lpk-categories', JsonApiController::class)->only('index','show','store','update');
    // Route::patch('contracts/{id}/status', [ContractController::class, 'updateStatus']);
    $server->resource('roles', RoleController::class)->only('index','show','store','update','delete');
    // $server->resource('payments', PaymentController::class)->only('index','show','store','update');
    $server->resource('payments', PaymentController::class)->only('index', 'show', 'store', 'update')->actions(function ($actions) {
        // Define custom route for importing payments
        $actions->post('import');
    });
    $server->resource('duiku-pop-payment-transaction', JsonApiController::class)->only('index', 'show');
    $server->resource('notifications', NotificationController::class)->only('index','show','store','update','deleted')->actions(function ($actions) {
        $actions->withId()->get('content');
        $actions->withId()->get('student');
    });

    Route::prefix('dashboard')->group(function() {
        Route::get('summary',[ReportSummaryController::class, 'getSummary']);
        Route::get('summary/student',[ReportSummaryController::class, 'GetStudentStatus']);
        Route::get('student/weekly',[ReportSummaryController::class, 'getRegisterWeekly']);
        Route::get('student/yearly',[ReportSummaryController::class, 'getRegisterYearly']);
        Route::get('principal',[ReportSummaryController::class, 'getPrincipal']);
        Route::get('provision',[ReportSummaryController::class, 'getProvision']);
        Route::get('registered/finised',[ReportSummaryController::class, 'getRegisteredSteps']);
        Route::get('payment/yearly',[ReportSummaryController::class, 'getPaymentYearly']);
    });



});


Route::get('/student/v1/user/app-url', [ConfigLpkUserController::class, 'getUrlApp']);
Route::get('/student/v1/user/app-url-1', [ConfigLpkUserController::class, 'getUrlApp']);
JsonApiRoute::server('student-v1')->middleware('auth:students')->prefix('student/v1')->resources(function ($server) {
    Route::get('/user/profile', [StudentUserController::class, 'profile']);
    Route::get('/user/history-payment', [StudentUserController::class, 'historyPayment']);
    Route::get('/user/contract', [StudentUserController::class, 'contract']);
    Route::get('/user/installment', [StudentUserController::class, 'installment']);
    Route::get('/user/installment-active', [StudentUserController::class, 'installmentActive']);
    Route::get('/user/identity', [StudentUserController::class, 'identity']);
    Route::get('/user/bank-account', [StudentUserController::class, 'bankAccount']);
    // Route::get('/user/required-document', [StudentUserController::class, 'requiredDoc']);

    Route::patch('/user/required-document/ktp-pemohon-penjamin', [StudentUserController::class, 'updateRequiredDocktpPemohonPenjamin']);
    Route::patch('/user/required-document/kartu-keluarga', [StudentUserController::class, 'updateRequiredDockartuKeluarga']);
    Route::patch('/user/required-document/ijazah-sekolah', [StudentUserController::class, 'updateRequiredDocIjazahSekolah']);
    Route::patch('/user/required-document/foto-keluarga', [StudentUserController::class, 'updateRequiredDocFotoKeluarga']);
    Route::patch('/user/required-document/kontrak-perjanjian-hire-purchase', [StudentUserController::class, 'updateRequiredDocKontrakPerjanjianHirePurchase']);
    Route::patch('/user/required-document/surat-permohonan-hire-purchase', [StudentUserController::class, 'updateRequiredDocSuratPermohonanHirePurchase']);
    Route::patch('/user/required-document/surat-kesanggupan-bayar', [StudentUserController::class, 'updateRequiredDocSuratKesanggupanBayar']);
    Route::patch('/user/required-document/hasil-pra-medical-check-up', [StudentUserController::class, 'updateRequiredDocHasilPraMedicalCheckUp']);
    Route::patch('/user/required-document/name-tag-lpk', [StudentUserController::class, 'updateRequiredDocNameTagLpk']);
    Route::patch('/user/required-document/certificate-of-eligibility', [StudentUserController::class, 'updateRequiredDocCertificateOfEligibility']);
    Route::patch('/user/required-document/surpervising-organization', [StudentUserController::class, 'updateRequiredDocSurpervisingOrganization']);
    Route::patch('/user/required-document/kontrak-kerja-di-jepang', [StudentUserController::class, 'updateRequiredDocKontrakKerjaDiJepang']);
    Route::patch('/user/required-document/tiket-pesawat-keberangkatan', [StudentUserController::class, 'updateRequiredDocTiketPesawatKeberangkatan']);
    Route::patch('/user/required-document/formulir-pendaftaran-koperasi', [StudentUserController::class, 'updateRequiredDocFormulirPendaftaranKoperasi']);
    Route::patch('/user/required-document/surat-persetujuan-orang-tua', [StudentUserController::class, 'updateRequiredDocSuratPersetujuanOrangTua']);
    Route::get('/user/required-document/{id}/surat-persetujuan-orang-tua', [StudentUserController::class, 'downloadDocSuratPersetujuanOrangTua']);
    // Benarkan kode ini
    // http://localhost/github/lpk-api/public//storage/uploads/notification_attachment/9d3ffbeb-4a96-4e42-9cca-95d3381668a8/9d3ffbeb-87e2-4ad2-aec2-cf7ee0d0c623/ijazah.png
    Route::get('/user/storage/uploads/notification_attachment/{notificationId}/{fileId}/{fileName}', [StudentUserController::class, 'downloadFileAttachment']);


    Route::get('/user/notification', [StudentUserController::class, 'notificationList']);
    Route::get('/user/notification/{id}', [StudentUserController::class, 'notificationGet']);
    Route::get('/user/notification/{id}/content', [StudentUserController::class, 'notificationContent']);
    Route::post('/user/notification/{id}/read', [StudentUserController::class, 'notificationRead']);



    Route::patch('/user/update-profile', [StudentUserController::class, 'UpdateProfile']);
    Route::patch('/user/update-japan-information', [StudentUserController::class, 'UpdateJapanInformation']);
    // $server->resource('students', UserController::class)->only('index','show','store','update');
    $server->resource('cities', JsonApiController::class)->only('index','show');
    $server->resource('categories', JsonApiController::class)->only('index','show');
    $server->resource('lpk-categories', JsonApiController::class)->only('index','show','store','update');

    Route::post('/user/create-payment', [DuitlkuPOPController::class, 'createPayment']);
    Route::post('/user/cancel-payment', [DuitlkuPOPController::class, 'cancelPayment']);
});
