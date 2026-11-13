<?php

namespace App\JsonApi\BackOffice\V1\StudentStatuses;

use App\Models\StudentStatus;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\Fields\Str;
use App\JsonApi\Filters\whereMonthYear;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentStatusSchema extends Schema
{
    protected $defaultSort = '-createdAt';
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = StudentStatus::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make()->uuid(),
            Str::make('status')->sortable(),
            Str::make('status_action')->sortable(),
            Str::make('status_remark')->sortable(),
            Str::make('name_by')->sortable(),
            // ArrayHash::make('student','student')->camelizeFields()->hidden(
            //     static fn($request) => method_exists($request, 'isUpdating') ? $request->isUpdating() : null
            //   ),
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
        return [];
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
