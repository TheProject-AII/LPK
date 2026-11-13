<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentChange extends Model
{
    use HasUuids, SoftDeletes;


    protected $casts = [
        'change_at' => 'date:d-m-Y',
    ];

    protected $appends = ['payment_change_cashier_name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_id',
        'payment_change_before',
        'payment_change_after',
        'payment_change_by_id',
        'payment_change_remark_before',
        'payment_change_remark_after'
    ];

                        

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function getPaymentChangeCashierNameAttribute()
    {
        $name = ($this->belongsTo(User::class, 'payment_change_by_id')->first())?$this->belongsTo(User::class, 'payment_change_by_id')->first()->name:null;

        return $name;
    }


}
