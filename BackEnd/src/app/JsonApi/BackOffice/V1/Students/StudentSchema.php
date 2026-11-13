<?php

namespace App\JsonApi\BackOffice\V1\Students;

use App\Models\Student;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\WhereIn;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\Map;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use LaravelJsonApi\Eloquent\Fields\ArrayList;
use App\JsonApi\Filters\whereMonthYear;
use App\JsonApi\Filters\whereHasLike;
use App\Interfaces\LpkDriver;
use LaravelJsonApi\Eloquent\Contracts\Driver;

class StudentSchema extends Schema
{


   /**
     * @return Driver
     */
    protected function driver(): Driver
    {
        return new LpkDriver($this->newInstance());
    }

    protected $defaultSort = '-createdAt';
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Student::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        if(Request::segment(count(Request::segments())) == "students"){
            return [
                ID::make()->uuid(),
                Str::make('studentName')->sortable(),
                Str::make('studentNo')->sortable(),
                Str::make('studentAddress')->sortable(),
                Str::make('studentKumiaiName')->hidden(),
                Str::make('studentKumiaiAddress')->hidden(),
                Str::make('studentEmployementInJapan')->hidden(),
                Str::make('studentAddressInJapan')->hidden(),
                Str::make('studentCompanyNameInJapan')->hidden(),
                Str::make('studentWorkProgramInJapan')->hidden(),
                Str::make('studentParentName')->sortable(),
                Str::make('studentParentRelationship')->sortable(),
                Str::make('studentParentPhoneNo')->sortable(),
                Str::make('password')->hidden(),
                Str::make('passwordConfirmation')->hidden(),
                Str::make('action')->hidden(),
                Str::make('remarks')->sortable(),
                // Str::make('studentLpkId')->sortable(),
                Str::make('studentEmail')->sortable(),
                // ArrayHash::make('studentPlaceOfBirth','studentPlaceOfBirth')->camelizeFields(),
                Map::make('studentPlaceOfBirth', [
                    Str::make('id'),
                    Str::make('cityName'),
                  ])->on('studentPlaceOfBirth'),
                // ArrayHash::make('studentLpk','studentLpk')->camelizeFields(),

                Map::make('studentContractActive', [
                    Str::make('id'),
                    Str::make('contractNo'),
                    Str::make('status'),
                  ])->on('studentContractActive'),
                Map::make('studentContractActiveInstallmentActive', [
                  Str::make('id'),
                  Str::make('contractNo'),
                  Str::make('installmentNo'),
                  Str::make('installmentContractId'),
                  Str::make('installmentMonthlyInstallment'),
                  Str::make('paymentAmount'),
                  Str::make('remainingPayment'),
                  DateTime::make('installmentStartDate'),
                  DateTime::make('installmentDueDate'),
                  Str::make('status'),
                ])->on('studentContractActiveInstallmentActive'),

                Map::make('studentLpk', [
                    Str::make('id'),
                    Str::make('lpkName'),
                  ])->on('studentLpk'),

                DateTime::make('studentDateOfBirth')->sortable(),
                // Str::make('studentLpkId')->sortable(),
                Str::make('studentType')->sortable(),
                Str::make('studentGender')->sortable(),
                Str::make('studentReligion')->sortable(),
                Str::make('studentBloodType')->sortable(),
                Str::make('studentLastEducation')->sortable(),
                Str::make('studentPhoneNo')->sortable(),
                Str::make('studentEmergencyPhoneNo')->sortable(),
                Str::make('status')->sortable(),
                Str::make('studentPhotoUrl')->sortable()->readOnly(),
                Str::make('studentPhoto')->hidden(),

                Str::make('ktpPemohonPenjaminUrl')->sortable()->readOnly(),
                Str::make('kartuKeluargaUrl')->sortable()->readOnly(),
                Str::make('ijazahSekolahUrl')->sortable()->readOnly(),
                Str::make('fotoKeluargaUrl')->sortable()->readOnly(),
                Str::make('kontrakPerjanjianHirePurchaseUrl')->sortable()->readOnly(),
                Str::make('suratPermohonanHirePurchaseUrl')->sortable()->readOnly(),
                Str::make('suratKesanggupanBayarUrl')->sortable()->readOnly(),
                Str::make('hasilPraMedicalCheckUpUrl')->sortable()->readOnly(),
                Str::make('nameTagLpkUrl')->sortable()->readOnly(),
                Str::make('certificateOfEligibilityUrl')->sortable()->readOnly(),
                Str::make('surpervisingOrganizationUrl')->sortable()->readOnly(),
                Str::make('kontrakKerjaDiJepangUrl')->sortable()->readOnly(),
                Str::make('tiketPesawatKeberangkatanUrl')->sortable()->readOnly(),
                Str::make('formulirPendaftaranKoperasiUrl')->sortable()->readOnly(),
                Str::make('suratPersetujuanOrangTuaUrl')->sortable()->readOnly(),

                Str::make('requiredDocktpPemohonPenjamin')->hidden(),
                Str::make('requiredDockartuKeluarga')->hidden(),
                Str::make('requiredDocijazahSekolah')->hidden(),
                Str::make('requiredDocfotoKeluarga')->hidden(),
                Str::make('requiredDockontrakPerjanjianHirePurchase')->hidden(),
                Str::make('requiredDocsuratPermohonanHirePurchase')->hidden(),
                Str::make('requiredDocsuratKesanggupanBayar')->hidden(),
                Str::make('requiredDochasilPraMedicalCheckUp')->hidden(),
                Str::make('requiredDocnameTagLpk')->hidden(),
                Str::make('requiredDoccertificateOfEligibility')->hidden(),
                Str::make('requiredDocsurpervisingOrganization')->hidden(),
                Str::make('requiredDockontrakKerjaDiJepang')->hidden(),
                Str::make('requiredDoctiketPesawatKeberangkatan')->hidden(),
                Str::make('requiredDocformulirPendaftaranKoperasi')->hidden(),
                Str::make('requiredDocsuratPersetujuanOrangTua')->hidden(),

                Str::make('studentIdentitys')->hidden(),
                Str::make('studentBankAccounts')->hidden(),
                Str::make('currentStatus','currentStatus')->sortable(),
                // ArrayHash::make('studentIdentitys','studentIdentitys')->camelizeFields(),
                // ArrayHash::make('studentLicenses','studentLicenses')->camelizeFields(),
                // ArrayHash::make('studentBankAccounts','studentBankAccounts')->camelizeFields(),

                // ArrayHash::make('studentIdentitys','studentIdentityList')->camelizeFields(),
                // ArrayHash::make('studentBankAccounts','studentBankAccountList')->camelizeFields(),
                DateTime::make('createdAt')->serializeUsing(
                    static fn($value) => Carbon::parse($value)->setTimezone(Auth::user()->timezone)->toDateTimeString()
                )->sortable()->readOnly(),

                DateTime::make('updatedAt')->serializeUsing(
                    static fn($value) => Carbon::parse($value)->setTimezone(Auth::user()->timezone)->toDateTimeString()
                )->sortable()->readOnly(),
            ];
        }
        else {
            return [
                ID::make()->uuid(),
                Str::make('studentName')->sortable(),
                Str::make('studentNo')->sortable(),
                Str::make('studentAddress')->sortable(),
                Str::make('studentKumiaiName')->sortable(),
                Str::make('studentKumiaiAddress')->sortable(),
                Str::make('studentEmployementInJapan')->sortable(),
                Str::make('studentAddressInJapan')->sortable(),
                Str::make('studentCompanyNameInJapan')->sortable(),
                Str::make('studentWorkProgramInJapan')->sortable(),
                Str::make('studentParentName')->sortable(),
                Str::make('studentParentRelationship')->sortable(),
                Str::make('studentParentPhoneNo')->sortable(),
                Str::make('password')->hidden(),
                Str::make('passwordConfirmation')->hidden(),
                Str::make('action')->hidden(),
                Str::make('remarks')->hidden(),
                Map::make('lpkCategory', [
                    Str::make('id'),
                    Str::make('categoryName'),
                  ])->on('lpkCategory'),
                // Str::make('studentLpkId')->sortable(),
                Str::make('studentEmail')->sortable(),
                // ArrayHash::make('studentPlaceOfBirth','studentPlaceOfBirth')->camelizeFields(),
                // ArrayHash::make('studentLpk','studentLpk')->camelizeFields(),
                Map::make('studentPlaceOfBirth', [
                    Str::make('id'),
                    Str::make('cityName'),
                  ])->on('studentPlaceOfBirth'),
                Map::make('studentLpk', [
                    Str::make('id'),
                    Str::make('lpkName'),
                  ])->on('studentLpk'),
                  Map::make('studentContractActive', [
                    Str::make('id'),
                    Str::make('contractNo'),
                    Str::make('contractStartDate'),
                    Str::make('contractEndDate'),
                    Str::make('contractTotalPenalty'),
                    Str::make('contractTotalPayable'),
                    Str::make('contractPaidPayment'),
                    Str::make('contractRemainingPayment'),
                    Str::make('status'),
                  ])->on('studentContractActive'),
                DateTime::make('studentDateOfBirth')->sortable(),
                // Str::make('studentLpkId')->sortable(),
                Str::make('studentType')->sortable(),
                Str::make('studentGender')->sortable(),
                Str::make('studentReligion')->sortable(),
                Str::make('studentBloodType')->sortable(),
                Str::make('studentLastEducation')->sortable(),
                Str::make('studentPhoneNo')->sortable(),
                Str::make('studentEmergencyPhoneNo')->sortable(),
                // Str::make('currentStatus','currentStatus')->sortable(),
                Str::make('status')->sortable(),
                Str::make('studentPhotoUrl')->sortable()->readOnly(),
                Str::make('studentPhoto')->hidden(),

                Str::make('ktpPemohonPenjaminUrl')->sortable()->readOnly(),
                Str::make('kartuKeluargaUrl')->sortable()->readOnly(),
                Str::make('ijazahSekolahUrl')->sortable()->readOnly(),
                Str::make('fotoKeluargaUrl')->sortable()->readOnly(),
                Str::make('kontrakPerjanjianHirePurchaseUrl')->sortable()->readOnly(),
                Str::make('suratPermohonanHirePurchaseUrl')->sortable()->readOnly(),
                Str::make('suratKesanggupanBayarUrl')->sortable()->readOnly(),
                Str::make('hasilPraMedicalCheckUpUrl')->sortable()->readOnly(),
                Str::make('nameTagLpkUrl')->sortable()->readOnly(),
                Str::make('certificateOfEligibilityUrl')->sortable()->readOnly(),
                Str::make('surpervisingOrganizationUrl')->sortable()->readOnly(),
                Str::make('kontrakKerjaDiJepangUrl')->sortable()->readOnly(),
                Str::make('tiketPesawatKeberangkatanUrl')->sortable()->readOnly(),
                Str::make('formulirPendaftaranKoperasiUrl')->sortable()->readOnly(),
                Str::make('suratPersetujuanOrangTuaUrl')->sortable()->readOnly(),

                Str::make('requiredDocktpPemohonPenjamin')->hidden(),
                Str::make('requiredDockartuKeluarga')->hidden(),
                Str::make('requiredDocijazahSekolah')->hidden(),
                Str::make('requiredDocfotoKeluarga')->hidden(),
                Str::make('requiredDockontrakPerjanjianHirePurchase')->hidden(),
                Str::make('requiredDocsuratPermohonanHirePurchase')->hidden(),
                Str::make('requiredDocsuratKesanggupanBayar')->hidden(),
                Str::make('requiredDochasilPraMedicalCheckUp')->hidden(),
                Str::make('requiredDocnameTagLpk')->hidden(),
                Str::make('requiredDoccertificateOfEligibility')->hidden(),
                Str::make('requiredDocsurpervisingOrganization')->hidden(),
                Str::make('requiredDockontrakKerjaDiJepang')->hidden(),
                Str::make('requiredDoctiketPesawatKeberangkatan')->hidden(),
                Str::make('requiredDocformulirPendaftaranKoperasi')->hidden(),
                Str::make('requiredDocsuratPersetujuanOrangTua')->hidden(),



                // ArrayHash::make('historyPayment','historyPayment')->camelizeFields(),
                // ArrayHash::make('studentIdentity','studentIdentityList')->camelizeFields(),
                // ArrayHash::make('studentBankAccounts','studentBankAccountList')->camelizeFields(),
                // ArrayList::make('studentIdentitys'),

                // Map::make('studentIdentitys', [
                //     Str::make('id'),
                //     Str::make('studentIdentityType'),
                //     Str::make('studentIdentityNo'),
                //     Str::make('studentIdentityExpireDate'),
                //     Str::make('studentIdentityAddress'),
                //     Str::make('studentIdentityPlaceOfIssue'),
                //     Str::make('studentIdentityFile'),
                //   ]),


                  Str::make('studentIdentitys')->hidden(),
                  Str::make('studentBankAccounts')->hidden(),
                // ArrayHash::make('studentContractActive','studentContractActive')->camelizeFields(),
                // ArrayHash::make('studentLicenses','studentLicenses')->camelizeFields(),
                // ArrayHash::make('studentBankAccounts','studentBankAccounts')->camelizeFields(),
                DateTime::make('createdAt')->serializeUsing(
                    static fn($value) => Carbon::parse($value)->setTimezone(Auth::user()->timezone)->toDateTimeString()
                )->sortable()->readOnly(),

                DateTime::make('updatedAt')->serializeUsing(
                    static fn($value) => Carbon::parse($value)->setTimezone(Auth::user()->timezone)->toDateTimeString()
                )->sortable()->readOnly(),
            ];
        }
    }

    /**
     * Get the resource filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return [

            WhereIdIn::make($this),
            // whereIn::make('currentStatus')->delimiter(','),
            whereIn::make('status')->delimiter(','),
            whereIn::make('studentType')->delimiter(','),
            whereIn::make('studentGender')->delimiter(','),
            whereIn::make('studentReligion')->delimiter(','),
            whereIn::make('studentBloodType')->delimiter(','),
            whereIn::make('studentLastEducation')->delimiter(','),
            whereIn::make('studentPlaceOfBirth','student_place_of_birth_id')->delimiter(','),
            whereIn::make('studentLpk','lpk_id')->delimiter(','),
            where::make('studentName')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),
              where::make('studentEmail')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),
              where::make('studentNo')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),
            where::make('studentAddress')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),
            where::make('studentEmail')->using('like')->deserializeUsing(
                  fn($value) => '%'.$value.'%'
              ),
            where::make('studentPhoneNo')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
             ),
            where::make('studentEmergencyPhoneNo')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
             ),
            whereMonthYear::make('createdAtMonthYear','created_at'),
        ];
    }

    /**
     * Get the resource paginator.
     *
     * @return Paginator|null
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }
}
