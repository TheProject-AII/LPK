<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Role extends SpatieRole
{

    use HasFactory,HasUuids,SoftDeletes, SoftDeletes;





    /**
     * @return array
     */
    public function getPermissionModelAttribute()
    {
        return $this->permissions->groupBy('model')->map(fn($data) => $data->pluck('name'));
    }


    public function roleLpk()
    {
        return $this->belongsTo(Lpk::class, 'lpk_id')->withTrashed();
    }
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
        'lpk_id'
    ];


}
