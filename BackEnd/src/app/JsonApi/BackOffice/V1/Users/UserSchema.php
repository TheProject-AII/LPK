<?php

namespace App\JsonApi\BackOffice\V1\Users;

use App\Models\User;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\Map;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Filters\WhereIn;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use App\JsonApi\Filters\whereMonthYear;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use LaravelJsonApi\Eloquent\Fields\ArrayList;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Interfaces\LpkDriver;
use LaravelJsonApi\Eloquent\Contracts\Driver;

class UserSchema extends Schema
{



   /**
     * @return Driver
     */
    protected function driver(): Driver
    {
        return new LpkDriver($this->newInstance());
    }

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = User::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make()->uuid(),
            Str::make('name')->sortable(),
            Str::make('email')->sortable(),
            Str::make('active')->sortable(),
            Str::make('password')->hidden(),
            Str::make('password_confirmation')->hidden(),
            Str::make('desireStatus')->hidden(),
            Map::make('userLpk', [
                Str::make('id'),
                Str::make('lpkName'),
              ])->on('userLpk'),
            ArrayList::make('roles')->hidden(),
            ArrayHash::make('rolesName','rolesName')->camelizeFields(),
            ArrayHash::make('permissions','permissionModel')->camelizeFields(),
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
            whereIn::make('userLpk','lpk_id')->delimiter(','),
            whereIn::make('active')->delimiter(','),
            where::make('name')->using('like')->deserializeUsing(
                fn($value) => '%'.$value.'%'
              ),
              where::make('email')->using('like')->deserializeUsing(
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
