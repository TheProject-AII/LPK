<?php

namespace App\JsonApi\Public\v1\Lpks;

use App\JsonApi\Filters\whereMonthYear;
use App\Models\Lpk;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIn;

class LpkSchema extends Schema
{

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Lpk::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make()->uuid(),
            Str::make('lpkNo')->sortable(),
            Str::make('lpkName')->sortable(),
            Str::make('lpkBusinessType')->sortable(),
            Str::make('lpkPhoneNo')->sortable(),
            Str::make('lpkEmail')->sortable(),
            Str::make('lpkAddress')->sortable(),
            Str::make('lpkContactPersonIdentityNo')->sortable(),
            Str::make('lpkContactPersonPassportNo')->sortable(),
            Str::make('lpkContactPersonNationality')->sortable(),
            Str::make('lpkContactPersonOccupation')->sortable(),
            Str::make('lpkContactPersonAddress')->sortable(),
            Str::make('lpkContactPersonDateOfBirth')->sortable(),
            Str::make('lpkContactPersonPlaceOfBirth')->sortable(),
            Str::make('lpkContactPersonPhoneNo')->sortable(),
            Str::make('lpkContactPersonName')->sortable(),
            DateTime::make('createdAt')->sortable()->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
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
            WhereIn::make('lpkBusinessType')->delimiter(','),
            Where::make('lpkName')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),
            where::make('lpkAddress')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),
            where::make('lpkEmail')->using('like')->deserializeUsing(
                  fn($value) => '%'.$value.'%'
              ),
            where::make('lpkPhoneNo')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
             ),
             where::make('lpkContactPersonIdentityNo')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
             ),
            where::make('lpkContactPersonPassportNo')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
             ),
             where::make('lpkContactPersonName')->using('like')->deserializeUsing(
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
