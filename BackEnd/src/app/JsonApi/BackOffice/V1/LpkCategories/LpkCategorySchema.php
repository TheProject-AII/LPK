<?php

namespace App\JsonApi\BackOffice\V1\LpkCategories;

use App\Models\LpkCategory;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\WhereIn;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\Fields\Map;
use LaravelJsonApi\Eloquent\Fields\Str;
use App\JsonApi\Filters\whereMonthYear;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LpkCategorySchema extends Schema
{
    protected $defaultSort = '-createdAt';
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = LpkCategory::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make()->uuid(),
            Str::make('lpkCategoryId')->sortable(),
            Str::make('categoryName')->sortable(),
            Str::make('lpkCategoryLpkId')->hidden(),
            Map::make('lpk', [
                Str::make('id'),
                Str::make('lpkNo'),
                Str::make('lpkName'),
              ])->on('lpk'),
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
            whereIn::make('lpkCategoryId')->delimiter(','),
            whereIn::make('lpk','lpk_category_lpk_id')->delimiter(','),

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
