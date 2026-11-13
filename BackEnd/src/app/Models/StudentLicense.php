<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentLicense extends Model implements HasMedia
{
    use  HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;

    protected $appends = [
        'student_license_file'
    ];


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('student_attachment_license')->singleFile()->useDisk('student_attachment_license');
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'student_license_type',
        'student_license_no',
        'student_license_expire_date',
        'student_license_address'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'media',
        'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];


     /**
     * @return array
     */
    public function getStudentLicenseFileAttribute()
    {
        $mediaItems = $this->getMedia("*");
        return count($mediaItems)!=0?$mediaItems[0]->getFullUrl():null;
    }

}
