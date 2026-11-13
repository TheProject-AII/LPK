<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lpk extends Model
{
    use  HasFactory,HasUuids, SoftDeletes;

    public static function boot() {


	    parent::boot();


	    // static::created(function($item) {
        //     $studentStatus = new Lpk;
        //     $studentStatus->student_id = $item->id;
        //     $studentStatus->status = 1;
        //     $studentStatus->status_by_id = Auth::id();
        //     $studentStatus->save();
	    // });


	    static::creating(function($item) {

            $item->lpk_no = IdGenerator::generate(['table' => 'lpks', 'length' => 20, 'field' => 'lpk_no', 'prefix' => 'LPK/'.date('Y').'/'.date('m').'/', 'reset_on_prefix_change' => true]);

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
        'lpk_no',
        'lpk_name',
        'lpk_business_type',
        'lpk_phone_no',
        'lpk_email',
        'lpk_address',
        'lpk_contact_person_identity_no',
        'lpk_contact_person_passport_no',
        'lpk_contact_person_nationality',
        'lpk_contact_person_occupation',
        'lpk_contact_person_address',
        'lpk_contact_person_date_of_birth',
        'lpk_contact_person_place_of_birth',
        'lpk_contact_person_phone_no',
        'lpk_contact_person_name',
        'lpk_bank_account_number',
        'lpk_bank_account_name',
        'lpk_bank_branch',

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


    // public function lpkBusinessType()
    // {
    //      return $this->belongsTo(Category::class, 'lpk_business_type')->withTrashed();
    // }

    public function lpkCategory()
    {
         return $this->hasMany(LpkCategory::class,'lpk_category_lpk_id')->select('lpk_category_id','id');

     }

     public function lpkContactPersonPlaceOfBirth()
    {
        return $this->belongsTo(City::class, 'lpk_contact_person_place_of_birth')->withTrashed();
    }
}
