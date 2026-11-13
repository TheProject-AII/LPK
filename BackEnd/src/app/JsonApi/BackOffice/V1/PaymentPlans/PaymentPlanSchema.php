<?php

namespace App\JsonApi\BackOffice\V1\PaymentPlans;

use App\Models\PaymentPlan;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\Map;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\Fields\Str;
use App\JsonApi\Filters\whereMonthYear;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentPlanSchema extends Schema
{




    protected $defaultSort = '-createdAt';
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = PaymentPlan::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make()->uuid(),
            Number::make('paymentPlanRange')->sortable(),
            Number::make('paymentPlanInterest')->sortable(),
            Number::make('paymentPlanInterestProvision')->sortable(),
            Str::make('paymentPlanName')->sortable(),
            Str::make('paymentPlanLpkId')->hidden(),
            Map::make('paymentPlanLpk', [
                Str::make('id'),
                Str::make('lpkNo'),
                Str::make('lpkName'),
              ])->on('paymentPlanLpk'),
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
            whereIn::make('paymentPlanLpk','payment_plan_lpk_id')->delimiter(','),
            where::make('paymentPlanName')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),
            where::make('paymentPlanRange')->using('like')->deserializeUsing(
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
