<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installment extends Model
{
    use HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['installment_contract_id','installment_monthly_installment','installment_penalty','installment_start_date','installment_due_date','installment_remarks'];

    protected $appends = ['payment_amount','remaining_payment'];


    public function contract()
    {

         return $this->belongsTo(Contract::class, 'installment_contract_id')->withTrashed();

    }

    public function historyPayment()
    {
        //  return $this->hasMany(Payment::class, 'payment_installment_id')->withTrashed();
         return $this->hasMany(PaymentUse::class, 'installment_id')->withTrashed();
    }

    public function additionalFee()
    {
         return $this->hasMany(InstallmentAdditionalFee::class, 'installment_id')->withTrashed();
    }


    public function historyStatus()
    {
         return $this->hasMany(InstallmentStatus::class,'installment_id')->withTrashed()->orderBy('created_at');

     }

     public function currentStatus()
    {
         return $this->hasOne(InstallmentStatus::class,'installment_id')->withTrashed()->latest();

     }

     public function currentStatusSequence()
    {
         return $this->hasOne(InstallmentStatus::class,'installment_id')->withTrashed()->orderBy('installment_sequence_no', 'asc')->latest();

     }

     public function currentStatusOldest()
    {
         return $this->hasOne(InstallmentStatus::class,'installment_id')->withTrashed()->orderBy('installment_id', 'asc');

     }


     public function getInstallmentPenaltyAttribute()
        {
            $installmentPenalty = $this->additionalFee()->sum('installment_additional_fee');

            return $installmentPenalty ? (int)$installmentPenalty : 0;
        }

     public function getPaymentAmountAttribute()
        {
            $paymentAmount = $this->historyPayment()->sum('payment_use');

                return (int)$paymentAmount;
        }

    public function getRemainingPaymentAttribute()
        {
            $remainingPayment = $this->installment_monthly_installment + $this->installment_penalty - $this->paymentAmount;

                return (int)$remainingPayment;
        }

}
