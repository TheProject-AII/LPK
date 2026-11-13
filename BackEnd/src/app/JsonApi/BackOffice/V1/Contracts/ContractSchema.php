<?php

namespace App\JsonApi\BackOffice\V1\Contracts;

use App\Models\Contract;
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
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use LaravelJsonApi\Eloquent\Fields\Map;
use App\JsonApi\Filters\whereMonthYear;
use App\JsonApi\Filters\whereHasLike;
use App\Interfaces\LpkDriver;
use LaravelJsonApi\Eloquent\Contracts\Driver;

class ContractSchema extends Schema
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
    public static string $model = Contract::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make()->uuid(),
            // Str::make('contractStudent','student_id')->sortable(),
            Map::make('contractStudent', [
              Str::make('id'),
                Str::make('studentName'),
                Str::make('studentNo'),
                Str::make('studentAddress'),
                Str::make('studentEmail'),
                Str::make('studentDateOfBirth'),
                Str::make('studentGender'),
                Str::make('studentBloodType'),
                Str::make('studentReligion'),
                Str::make('studentLastEducation'),
                Str::make('studentPhoneNo'),
                Str::make('studentEmergencyPhoneNo'),
                Map::make('studentPlaceOfBirth', [
                  Str::make('id'),
                  Str::make('cityName'),
                ])->on('studentPlaceOfBirth'),
            ])->on('contractStudent')->hidden(
              static fn($request) => method_exists($request, 'isUpdating') ? $request->isUpdating() : null
            ),
            Map::make('contractLpk', [
              Str::make('id'),
              Str::make('lpkName'),
              Str::make('lpkBankAccountNumber'),
              Str::make('lpkBankAccountName'),
              Str::make('lpkBankBranch'),
            ])->on('contractLpk'),
            Map::make('contractPaymentPlan', [
              Str::make('id'),
              Str::make('paymentPlanName'),
              Str::make('paymentPlanRange'),
            ])->on('contractPaymentPlan')->hidden(
              static fn($request) => method_exists($request, 'isUpdating') ? $request->isUpdating() : null
            ),
            // Str::make('contractLpk','lpk_id')->sortable(),
            // ArrayHash::make('contractStudent','contractStudent')->camelizeFields()->hidden(
            //     static fn($request) => method_exists($request, 'isUpdating') ? $request->isUpdating() : null
            //   ),
              // ArrayHash::make('contractInstallment','contractInstallment')->camelizeFields()->hidden(
              //   static fn($request) => method_exists($request, 'isUpdating') ? $request->isUpdating() : null
              // ),
              // ArrayHash::make('contractInstallment','contractInstallment')->camelizeFields()->hidden(),
            // Str::make('currentStatus','currentStatus')->sortable(),
            Str::make('status')->sortable()->readOnly(),
            Str::make('action')->hidden(),

            // ArrayHash::make('contractLpk','contractLpk')->camelizeFields()->hidden(
            //   static fn($request) => method_exists($request, 'isUpdating') ? $request->isUpdating() : null
            // ),
            // ArrayHash::make('installmentActive','installmentActive')->camelizeFields(),
            // ArrayHash::make('contractPaymentPlan','contractPaymentPlan')->camelizeFields()->hidden(
            //   static fn($request) => method_exists($request, 'isUpdating') ? $request->isUpdating() : null
            // ),
            Str::make('contractNo')->sortable(),
            Str::make('contractRemark')->sortable(),
            Str::make('contractGracePeriod')->sortable(),
            Str::make('contractRemainingPayment')->sortable(),
            Str::make('contractDateOfDeparture')->sortable(),
            Str::make('contractInitialContractInJapan')->sortable(),
            Str::make('contractEndOfContractInJapan')->sortable(),
            Str::make('contractFirstWorkingDay')->sortable(),
            Str::make('contractPayday')->sortable(),
            Str::make('contractProcessFee')->sortable(),
            Str::make('contractMembership')->sortable(),
            Str::make('contractProvisionFeeEnabled')->sortable(),
            Str::make('contractTaxEnabled')->sortable(),
            Str::make('contractTaxRate')->sortable(),
            Str::make('contractPrinciple')->sortable(),
            Str::make('contractPaidPayment')->sortable(),
            Str::make('contractTotalPayable')->sortable()->hidden(
              static fn($request) => method_exists($request, 'isUpdating') ? $request->isUpdating() : null
            ),
            Str::make('contractTotalPenalty')->sortable()->hidden(
                static fn($request) => method_exists($request, 'isUpdating') ? $request->isUpdating() : null
              ),
            // DateTime::make('contractStartDate')->sortable()->hidden(
            //   static fn($request) => method_exists($request, 'isUpdating') ? $request->isUpdating() : null
            // ),
            Str::make('contractDisbursmentDate')->sortable(),
            Str::make('contractWageDaily')->sortable(),
            Str::make('contractCutOffDate')->sortable(),
            Str::make('contractDaysOff')->sortable(),
            Str::make('contractKursYen')->sortable(),
            Str::make('contractProvisionAlreadyPaid')->sortable(),
            Str::make('contractStudentNeedTraining')->sortable(),
            Str::make('contractStudentNeedTraining')->sortable(),
            DateTime::make('contractEndDate')->sortable()->hidden(
              static fn($request) => method_exists($request, 'isUpdating') ? $request->isUpdating() : null
            ),
            Str::make('contractAttachmentFormulirPendaftaranAnggotaBaruUrl')->readOnly(),
            Str::make('contractAttachmentSuratPermohonanSewaBeliUrl')->readOnly(),
            Str::make('contractAttachmentSuratPernyataanKesanggupanMembayarUrl')->readOnly(),
            Str::make('contractAttachmentPerjanjianSewaBeliUrl')->readOnly(),
            Str::make('contractAttachmentSuratPernyataanDanJaminanUrl')->readOnly(),
            Str::make('contractAttachmentTandaTerimaPenyerahanIjasahUrl')->readOnly(),

            Str::make('contractAttachmentFormulirPendaftaranAnggotaBaru')->hidden(),
            Str::make('contractAttachmentSuratPermohonanSewaBeli')->hidden(),
            Str::make('contractAttachmentSuratPernyataanKesanggupanMembayar')->hidden(),
            Str::make('contractAttachmentPerjanjianSewaBeli')->hidden(),
            Str::make('contractAttachmentSuratPernyataanDanJaminan')->hidden(),
            Str::make('contractAttachmentTandaTerimaPenyerahanIjasah')->hidden(),
            // Str::make('status')->sortable()->readOnly(),
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
            whereIn::make('contractStudent','student_id')->delimiter(','),
            // whereIn::make('contractStartDate')->delimiter(','),
            whereIn::make('status')->delimiter(','),
            whereIn::make('contractLpk','lpk_id')->delimiter(','),
            where::make('contractNo')->using('like')->deserializeUsing(
              fn($value) => '%'.$value.'%'
            ),

            whereHasLike::make('studentName','contractStudent','student_name'),
            where::make('contractTotalPayable')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),

              where::make('contractProcessFee')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),
              where::make('contractPrinciple')->using('like')->deserializeUsing(
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
