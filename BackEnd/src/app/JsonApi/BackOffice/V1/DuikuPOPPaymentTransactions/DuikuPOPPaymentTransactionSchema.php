<?php

namespace App\JsonApi\BackOffice\V1\DuikuPOPPaymentTransactions;

use App\Models\DuikuPOPPaymentTransaction;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Map;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\WhereIn;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\Number;
use App\JsonApi\Filters\whereMonthYear;
use App\JsonApi\Filters\whereHasLike;
use App\JsonApi\Filters\whereDateGreaterOrEqual;
use App\JsonApi\Filters\whereDateLessOrEqual;
use App\JsonApi\Filters\whereHasDuikuLpkName;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;

class DuikuPOPPaymentTransactionSchema extends Schema
{
    protected $defaultSort = '-createdAt';
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    
    public static string $model = DuikuPOPPaymentTransaction::class;
    public static string $type = 'duiku-pop-payment-transaction';
    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {

        return [
            ID::make()->uuid(),
            Str::make('merchantOrderId'),
            Map::make('student', [
                Str::make('id'),
                Str::make('studentName'),
                Str::make('studentNo'),
              ])->on('student'),

            ArrayHash::make('contract', 'contract_details')->camelizeFields(),

            ArrayHash::make('paymentUse','paymentUse')->camelizeFields(),
            Str::make('paymentMethodName')->sortable(),
            Number::make('baseAmount'),
            Number::make('adminFee'),
            Number::make('totalAmount'),
            Number::make('paidAmount'),
            Number::make('status'),
            DateTime::make('paidAt')->serializeUsing(
                static fn($value) => $value ? Carbon::parse($value)->setTimezone(Auth::user()->timezone ?? 'UTC')->toDateTimeString() : null
            )->sortable()->readOnly(),
            DateTime::make('createdAt')->serializeUsing(
                static fn($value) => $value ? Carbon::parse($value)->setTimezone(Auth::user()->timezone ?? 'UTC')->toDateTimeString() : null
            )->sortable()->readOnly(),
            DateTime::make('updatedAt')->serializeUsing(
                static fn($value) => $value ? Carbon::parse($value)->setTimezone(Auth::user()->timezone ?? 'UTC')->toDateTimeString() : null
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
            whereIn::make('paymentMethod', 'payment_method_code')->delimiter(','),
            whereHasLike::make('studentName','student','student_name'),
            where::make('paymentNo')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),
            whereMonthYear::make('createdAtMonthYear','created_at'),
            where::make('invoiceNo', 'merchant_order_id')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
            ),
            whereIn::make('status')->delimiter(','),
            whereDateGreaterOrEqual::make('paidAtStart', 'paid_at'),
            whereDateLessOrEqual::make('paidAtEnd', 'paid_at'),
            whereDateGreaterOrEqual::make('updatedAtStart', 'updated_at'),
            whereDateLessOrEqual::make('updatedAtEnd', 'updated_at'),
            whereHasDuikuLpkName::make('lpkName'),
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
