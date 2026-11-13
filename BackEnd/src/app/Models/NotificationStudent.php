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

class NotificationStudent extends Model
{
    use  HasFactory, HasUuids, SoftDeletes;

   
    protected $appends = ['student_name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'notification_id',
        'student_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];


    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }

    public function getStudentNameAttribute()
        {   
            return $this->student->student_name;

        }

}
