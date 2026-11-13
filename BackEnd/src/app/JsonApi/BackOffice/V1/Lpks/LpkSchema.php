<?php

namespace App\JsonApi\BackOffice\V1\Lpks;

use App\Models\Lpk;
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

class LpkSchema extends Schema
{
    protected $defaultSort = '-createdAt';
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
            // ArrayHash::make('category','category')->camelizeFields(),
            Str::make('lpkNo')->sortable(),
            Str::make('lpkName')->sortable(),
            Str::make('lpkPhoneNo')->sortable(),
            Str::make('lpkEmail')->sortable(),
            Str::make('lpkAddress')->sortable(),
            Str::make('lpkBankAccountNumber')->sortable(),
            Str::make('lpkBankAccountName')->sortable(),
            Str::make('lpkBankBranch')->sortable(),
            Str::make('active')->sortable(),
            Str::make('desireStatus')->hidden(),
            ArrayHash::make('lpkCategory','lpkCategory')->camelizeFields(),

            Str::make('lpkContactPersonIdentityNo')->sortable(),
            Str::make('lpkContactPersonPassportNo')->sortable(),
            Str::make('lpkContactPersonNationality')->sortable(),
            Str::make('lpkContactPersonOccupation')->sortable(),
            Str::make('lpkContactPersonPosition')->sortable(),
            Str::make('lpkContactPersonAddress')->sortable(),
            Str::make('lpkContactPersonDateOfBirth')->sortable(),
            // Str::make('lpkContactPersonPlaceOfBirth')->sortable(),
            Map::make('lpkContactPersonPlaceOfBirth', [
                Str::make('id'),
                Str::make('cityName'),
              ])->on('lpkContactPersonPlaceOfBirth'),
            Str::make('lpkContactPersonPhoneNo')->sortable(),
            Str::make('lpkContactPersonName')->sortable(),
            Str::make('password')->hidden(),
            Str::make('passwordConfirmation')->hidden(),
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
            whereIn::make('lpkCategory')->delimiter(','),
            where::make('lpkNo')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),
              where::make('lpkName')->using('like')->deserializeUsing(
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
            whereIn::make('active')->delimiter(','),
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
