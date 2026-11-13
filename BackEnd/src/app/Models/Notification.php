<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Carbon\Carbon;


class Notification extends Model implements HasMedia
{
    use  HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;




    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'notification_type',
        'notification_at',
    ];

    protected $appends = [
        'notification_attachment_file',
        'notification_image_file'
    ];


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('notificationAttachment')->useDisk('notification_attachment');
        $this->addMediaCollection('notificationImage')->singleFile()->useDisk('notification_image');
    }



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
        'media',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];


    public function notificationStudent()
    {
        return $this->hasMany(NotificationStudent::class, 'notification_id')->select('student_id','id');
    }

    public function notificationStudentAll()
    {
        return $this->hasMany(NotificationStudent::class, 'notification_id');
    }


    public function notificationContent()
    {
        return $this->hasOne(NotificationContent::class, 'notification_id');
    }

    public function notificationLpk()
    {
        return $this->belongsTo(Lpk::class, 'lpk_id');
    }

    public function notificationCreatedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


     /**
     * @return array
     */
    public function getNotificationAttachmentFileAttribute()
    {


        $data = $this->getMedia("notificationAttachment")->map(function($mediaObject) {
            return [
                'id' => $mediaObject->id,
                'fileName' => $mediaObject->file_name,
                'url' => $mediaObject->getFullUrl()
            ];
        });
        return $data;
    }

     /**
     * @return array
     */
    public function getNotificationImageFileAttribute()
    {
        $mediaItems = $this->getMedia("notificationImage");
        return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }


    /**
     * @return array
     */
    public function getNotificationAtUtcAttribute()
    {
        $dateIn = Carbon::createFromFormat('Y-m-d H:i:s', $this->notification_at, $this->notificationCreatedBy->timezone);

        // Change the timezone to UTC
        $dateInUTC = $dateIn->setTimezone('UTC');
        return $dateInUTC;
    }


}
