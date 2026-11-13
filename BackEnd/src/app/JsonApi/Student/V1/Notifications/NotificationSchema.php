<?php

namespace App\JsonApi\Student\V1\Notifications;

use App\Models\Notification;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\WhereIn;
use LaravelJsonApi\Eloquent\Filters\Where;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\ArrayHash;
use App\JsonApi\Filters\whereMonthYear;
use LaravelJsonApi\Eloquent\Fields\Map;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Interfaces\LpkDriver;
use LaravelJsonApi\Eloquent\Contracts\Driver;

class NotificationSchema extends Schema
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
    public static string $model = Notification::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make()->uuid(),
            Str::make('notificationType')->sortable(),
            DateTime::make('notificationAt')->sortable(),
            Str::make('notificationSubject')->sortable(),
            Str::make('notificationInformation')->sortable(),
            Str::make('notificationContent')->hidden(),
            ArrayHash::make('notificationAttachmentFile','notificationAttachmentFile'),
            Str::make('notificationImageFile','notificationImageFile'),
            Str::make('notificationImage')->hidden(),
            Str::make('notificationAttachment')->hidden(),
            Str::make('statusRead')->sortable(),
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
            whereIn::make('notificationType')->delimiter(','),
            where::make('notificationSubject')->using('like')->deserializeUsing(
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
