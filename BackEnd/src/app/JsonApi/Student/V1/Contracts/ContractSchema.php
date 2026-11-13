<?php

namespace App\JsonApi\Student\V1\Contracts;

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
            Map::make('contractLpk', [
              Str::make('id'),
              Str::make('lpkName'),
            ])->on('contractLpk'),
            Map::make('contractPaymentPlan', [
              Str::make('id'),
              Str::make('paymentPlanName'),
              Str::make('paymentPlanRange'),
            ])->on('contractPaymentPlan'),
            Str::make('status')->sortable(),
            Str::make('contractNo')->sortable(),
            Str::make('contractRemark')->sortable(),
            Str::make('contractRemainingPayment')->sortable(),
            Str::make('contractPaidPayment')->sortable(),
            Str::make('contractDateOfDeparture')->sortable(),
            Str::make('contractInitialContractInJapan')->sortable(),
            Str::make('contractEndOfContractInJapan')->sortable(),
            Str::make('contractPayday')->sortable(),
            Str::make('contractProcessFee')->sortable(),
            Str::make('contractMembership')->sortable(),
            Str::make('contractProvisionFeeEnabled')->sortable(),
            Str::make('contractTaxEnabled')->sortable(),
            Str::make('contractTaxRate')->sortable(),
            Str::make('contractPrinciple')->sortable(),
            Str::make('contractTotalPayable')->sortable(),
            DateTime::make('contractStartDate')->sortable(),
            DateTime::make('contractEndDate')->sortable(),
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
            whereIn::make('contractStartDate')->delimiter(','),
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
