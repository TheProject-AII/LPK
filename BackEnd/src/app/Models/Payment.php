<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasUuids, SoftDeletes;

    public static function boot() {


	    parent::boot();


	    static::updated(function($item) {
            $paymentChanges = new PaymentChange;
            $paymentChanges->payment_id = $item->id;
            $paymentChanges->payment_change_before = $item->getOriginal('payment_receive_amount');
            $paymentChanges->payment_change_after = $item->payment_receive_amount;
            $paymentChanges->payment_change_by_id = auth()->user()->id ;
            $paymentChanges->payment_change_remark_before  = $item->getOriginal('payment_remark');
            $paymentChanges->payment_change_remark_after  = $item->payment_remark;
            $paymentChanges->save();
	    });





    }

    protected $casts = [
        // 'payment_at' => 'date:d-m-Y',
    ];

    protected $appends = ['payment_cashier_name',
        // 'billing_amount',
        'payment_ref_no'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['payment_student_id',
                            'payment_no',
                            'payment_method',
                            'payment_contract_id',
                            'payment_receive_amount',
                            'payment_installment_id',
                            'payment_change_amount',
                            'payment_cashier_id',
                            'payment_at',
                            'payment_remark'];

    //  payment_method==1 cash
    //  payment_method==2 transfer
    //  payment_method==3 debit
    //  payment_method==4 Virtual Account
    //  payment_method==5 Duitku Gateway


    public function paymentInstallment()
    {
        return $this->belongsTo(Installment::class, 'payment_installment_id');
    }

    public function paymentStudent()
    {
        return $this->belongsTo(Student::class, 'payment_student_id', 'id');
    }

    public function paymentContract()
    {
        return $this->belongsTo(Contract::class, 'payment_contract_id', 'id');
    }

    public function paymentUse()
    {
        return $this->hasMany(PaymentUse::class, 'payment_id')
                    ->whereNull('payment_duitku');
    }

    public function paymentChange()
    {
        return $this->hasMany(PaymentChange::class, 'payment_id');
    }


    public function paymentCashier()
    {
        return $this->belongsTo(User::class, 'payment_cashier_id');
    }

    public function getPaymentCashierNameAttribute()
    {
        $name = ($this->belongsTo(User::class, 'payment_cashier_id')->first())?$this->belongsTo(User::class, 'payment_cashier_id')->first()->name:null;

        return $name;
    }



    // public function getBillingAmountAttribute()
    // {
    //     $billing = 0;

    //     $installment = $this->belongsTo(Contract::class, 'payment_contract_id')->contractInstallment->first();

    //     $paidAmount = $this->where('payment_installment_id', $this->payment_installment_id)
    //         ->where('created_at', '<',  $this->created_at)->sum('payment_receive_amount');


    //         if (isset($installment)) {
    //             $billing = $installment->installment_monthly_installment + $installment->installment_penalty;
    //         }


    //     $bilingAmount = $billing - $paidAmount;

    //     return $bilingAmount;
    // }

    public function getPaymentRefNoAttribute(){
        $no = null;
        $data = $this->belongsTo(Installment::class, 'payment_installment_id')->first();

        if (isset($data)) {
            $no = $data->installment_no;
        }

        return $no;
    }
}
