<?php

namespace App\JsonApi\BackOffice\V1\Installments;

use App\Models\Installment;
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
use App\JsonApi\Filters\whereMonthYear;

class InstallmentSchema extends Schema
{



    protected $defaultSort = '-createdAt';
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Installment::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make()->uuid(),
            // ArrayHash::make('contract','contract')->camelizeFields(),
            Str::make('contract','installment_contract_id')->sortable(),
            ArrayHash::make('historyPayment','historyPayment')->camelizeFields(),
            Str::make('installmentNo')->sortable(),
            Str::make('installmentMonthlyInstallment')->sortable(),
            Str::make('installmentPenalty')->sortable(),
            Str::make('installmentSequenceNo')->sortable(),
            Str::make('paymentAmount')->sortable(),
            Str::make('remainingPayment')->sortable(),
            DateTime::make('installmentStartDate')->sortable(),
            DateTime::make('installmentDueDate')->sortable(),
            DateTime::make('status')->sortable(),
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
            whereIn::make('contract','installment_contract_id')->delimiter(','),
            whereIn::make('installmentStartDate')->delimiter(','),
            whereIn::make('installmentDueDate')->delimiter(','),
            whereIn::make('installmentSequenceNo')->delimiter(','),
            whereIn::make('status')->delimiter(','),
            where::make('installmentNo')->using('like')->deserializeUsing(
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
