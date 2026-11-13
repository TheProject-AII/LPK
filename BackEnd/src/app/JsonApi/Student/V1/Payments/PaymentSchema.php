<?php

namespace App\JsonApi\Student\V1\Payments;

use App\Models\Payment;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Map;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\Fields\Str;
use App\JsonApi\Filters\whereMonthYear;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;

class PaymentSchema extends Schema
{
    protected $defaultSort = '-createdAt';
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Payment::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {

        return [
            ID::make()->uuid(),
            Str::make('paymentNo'),
              Map::make('paymentCashier', [
                Str::make('id'),
                Str::make('name'),
              ])->on('paymentCashier'),
            
            ArrayHash::make('paymentUse','paymentUse')->camelizeFields(),
            Str::make('paymentMethod')->sortable(),
            Str::make('paymentReceiveAmount'),
            Str::make('paymentTransferNo'),
            Str::make('paymentAt'),
            Str::make('paymentRemark')->sortable(),
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
            // WhereIdIn::make($this),
            // where::make('categoryName')->using('like')->deserializeUsing(
            //     fn($value) => '%'.$value.'%'
            //   ),
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
