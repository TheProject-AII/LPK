<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallmentStatus extends Model
{
    use  HasUuids,SoftDeletes;

    public static function boot() {


	    parent::boot();


	    static::created(function($item) {

            $installment = Installment::find($item->installment_id);
            $installment->status = $item->status;
            $installment->save();

	    });


	    

	}


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['installment_id','status','status_ref_id','status_action','status_by_id'];

    //status: 0:register  1:partial paid 2:paid 3: unpaid 4: active


    public function getStatusAttribute($status)
    {
        $now = Carbon::now();
        $data = $this->installment()->get();
        $statuReturn=$status;
        if($data[0]){
            if($status == 0){
                //tambah 1 hari untuk start date and due date dikarenakan tagihan baru muncul setelah melewati due date
                $installment_start_date_add_1= Carbon::parse($data[0]['installment_start_date'])->addDays(1);
                $installment_due_date_add_1= Carbon::parse($data[0]['installment_due_date'])->addDays(1);
                $installment_start_date= Carbon::parse($data[0]['installment_start_date']);
                $installment_due_date= Carbon::parse($data[0]['installment_due_date']);
                // if ($now->between($installment_start_date, $installment_due_date)) {
                //     $statuReturn = 4;
                // }
                // if($now > $installment_due_date){
                //     $statuReturn = 3;
                // }
                if ($now == $installment_start_date) {
                    $statuReturn = 4;
                }
                if($now > $installment_start_date_add_1){
                    $statuReturn = 3;
                }
            }

        }
        return $statuReturn;
        // $statuReturn = 3;
    }

    public function installment()
    {
         return $this->belongsTo(Installment::class, 'installment_id')->withTrashed();

    }




    public function user()
    {
         return $this->belongsTo(User::class, 'status_by_id')->withTrashed();

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
