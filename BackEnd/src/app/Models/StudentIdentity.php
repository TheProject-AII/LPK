<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentIdentity extends Model implements HasMedia
{
    use  HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;


    protected $appends = [
        'student_identity_file'
    ];


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('studentAttachmentIdentity')->singleFile()->useDisk('student_attachment_identity');
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
        'student_identity_type',
        'student_identity_no',
        'student_identity_name_of_educational_institution',
        'student_identity_expire_date',
        'student_identity_place_of_issue',
        'student_identity_address'
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
    public function getStudentIdentityFileAttribute()
    {
        $mediaItems = $this->getMedia("*");
        return count($mediaItems)!=0?$mediaItems[0]->getFullUrl():null;
    }

}
