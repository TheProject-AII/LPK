<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Scopes\LpkScope;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, HasRoles, SoftDeletes;

    // protected static function booted()
    // {
    //     static::addGlobalScope(new LpkScope);
    // }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'lpk_id',
        'password',
        'google_two_fa_secret',
        'google_id',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_two_fa_secret',
        'google_id',
        'deleted_at'
    ];



    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * @return array
     */
    public function getPermissionModelAttribute()
    {
        return $this->getAllPermissions()->groupBy('model')->map(fn($data) => $data->pluck('name'));
    }

    public function userLpk()
    {
        return $this->belongsTo(Lpk::class, 'lpk_id');
    }


    /**
     * @return array
     */
    public function getRolesNameAttribute()
    {
        return $this->roles->map(function($role){
            return [
                'id' => $role['id'],
                'name' => $role['name']
            ];
        });
    }


}
