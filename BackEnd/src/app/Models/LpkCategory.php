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

class LpkCategory extends Model
{
    use  HasFactory, HasUuids, SoftDeletes;


    protected $appends = ['category_name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lpk_category_lpk_id',
        'lpk_category_id',
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


    public function category()
    {
        return $this->belongsTo(Category::class, 'lpk_category_id');
    }

    public function lpk()
    {
        return $this->belongsTo(Lpk::class, 'lpk_category_lpk_id');
    }

    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->category_name : null;
    }

}
