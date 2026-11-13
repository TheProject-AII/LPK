<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractStatus extends Model
{
    use  HasFactory,HasUuids, SoftDeletes;

    public static function boot() {


	    parent::boot();


	    static::created(function($item) {

            $contract = Contract::find($item->contract_id);
            $contract->status = $item->status;
            $contract->save();

	    });


	    static::creating(function($item) {

	        // \Log::info('Item Creating Event:'.$item);

	    });



	}
   /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

     /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
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
        'contract_id',
        'status',
        'status_ref_id',
        'status_action',
        'status_by_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    public function contract()
    {
         return $this->belongsTo(Contract::class, 'contract_id')->withTrashed();

    }

    public function user()
    {
         return $this->belongsTo(User::class, 'status_by_id');

    }

    public function getNameByAttribute()
        {
            $user_name = "System";
            if($this->status_by_id!=0){
               $user_name = $this->user()->first()->name;
            }

                return $user_name;
        }
}
