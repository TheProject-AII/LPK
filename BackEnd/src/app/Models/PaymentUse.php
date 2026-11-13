<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentUse extends Model
{
    use HasUuids, SoftDeletes;

    protected $hidden = ['deleted_at'];
 
    protected $appends = ['installment'];

    protected $fillable = [
        'payment_id',
        'installment_id',
        'payment_use',
        'payment_duitku' // Nama kolom di DB
    ];

    /**
     * Relasi untuk transaksi Manual
     * Filter: payment_duitku IS NULL
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * Relasi untuk transaksi Duitku
     * Filter: payment_duitku == 1
     */
    public function duitku_payment()
    {
        return $this->belongsTo(DuikuPOPPaymentTransaction::class, 'payment_id');
    }

    public function installment_relation()
    {
        return $this->belongsTo(Installment::class, 'installment_id');
    }

    /**
     * Accessor untuk memunculkan data installment secara otomatis
     */
    public function getInstallmentAttribute()
    {
        // Mengambil data installment_no dari relasi
        return $this->installment_relation()
                    ->select('id', 'installment_no')
                    ->first();
    }
}