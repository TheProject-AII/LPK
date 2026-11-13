<?php

namespace App\JsonApi\Student\V1\Students;

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
use Carbon\Carbon;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\Map;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use App\JsonApi\Filters\whereMonthYear;

class StudentSchema extends Schema
{
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

        
        return [
            ID::make()->uuid(),
            Str::make('studentName')->sortable(),
            Str::make('lpkId')->sortable(),
            Str::make('studentNo')->sortable(),
            Str::make('studentAddress')->sortable(),
            Str::make('studentEmail')->sortable(),
            ArrayHash::make('studentPlaceOfBirth','studentPlaceOfBirth')->camelizeFields(),
            DateTime::make('studentDateOfBirth')->sortable(),
            Str::make('studentType')->sortable(),
            Str::make('studentGender')->sortable(),
            Str::make('studentReligion')->sortable(),
            Str::make('studentBloodType')->sortable(),
            Str::make('studentLastEducation')->sortable(),
            Str::make('studentPhoneNo')->sortable(),
            Str::make('studentEmergencyPhoneNo')->sortable(),
            Str::make('studentKumiaiName')->sortable(),
            Str::make('studentKumiaiAddress')->sortable(),
            Str::make('studentCompanyNameInJapan')->sortable(),
            Str::make('studentWorkProgramInJapan')->sortable(),
            ArrayHash::make('studentEmployementInJapan','studentEmployementInJapan')->camelizeFields(),
            Str::make('studentAddressInJapan')->sortable(),
            Str::make('studentParentName')->sortable(),
            Str::make('studentParentRelationship')->sortable(),
            Str::make('studentParentPhoneNo')->sortable(),
            Str::make('status')->sortable()->readOnly(),
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
            ArrayHash::make('studentIdentitys','studentIdentitys')->camelizeFields(),
            ArrayHash::make('studentBankAccounts','studentBankAccounts')->camelizeFields(),
            DateTime::make('createdAt')->serializeUsing(
                static fn($value) => Carbon::parse($value)->setTimezone(Auth::user()->timezone)->toDateTimeString()
              )->sortable()->readOnly(),

            DateTime::make('updatedAt')->serializeUsing(
                static fn($value) => Carbon::parse($value)->setTimezone(Auth::user()->timezone)->toDateTimeString()
              )->sortable()->readOnly(),
        ];
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
            whereIn::make('status')->delimiter(','),
            whereIn::make('studentType')->delimiter(','),
            whereIn::make('studentGender')->delimiter(','),
            whereIn::make('studentReligion')->delimiter(','),
            whereIn::make('studentBloodType')->delimiter(','),
            whereIn::make('studentLastEducation')->delimiter(','),
            whereIn::make('status')->delimiter(','),
            whereIn::make('studentPlaceOfBirth','student_place_of_birth_id')->delimiter(','),
            where::make('studentName')->using('like')->deserializeUsing(
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
