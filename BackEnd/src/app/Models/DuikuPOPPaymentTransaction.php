<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;

class DuikuPOPPaymentTransaction extends Model
{
    use  HasFactory, HasUuids, SoftDeletes;


    protected $table = 'duiku_pop_payment_transactions';
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
        'student_id',
        'merchant_order_id',
        'duitku_reference',
        'base_amount',
        'admin_fee',
        'total_amount',
        'paid_amount',
        'installment_ids',
        'payment_method_code',
        'status', //'PENDING','SUCCESS','FAILED','EXPIRED','CANCELED'
        'callback_payload',
        'paid_at',
    ];

    protected $appends = [
        'contract_details', 
        'payment_method_name'
    ];


    public static $duitkuMapping = [
        // Virtual Account (VA)
        'BC' => 'BCA Virtual Account',
        'M2' => 'Mandiri Virtual Account',
        'I1' => 'BNI Virtual Account',
        'BR' => 'BRI Virtual Account',
        'B1' => 'Permata Virtual Account',
        'BT' => 'BTN Virtual Account',
        'A1' => 'ATM Bersama',
        'CK' => 'CIMB Niaga Virtual Account',
        'NC' => 'Nobu Virtual Account',
        'VA' => 'Maybank Virtual Account',
        'SA' => 'Sampoerna Virtual Account',
        'BS' => 'BSI Virtual Account',
        'DB' => 'Danamon Virtual Account',

        // Wallet / QRIS
        'NQ' => 'QRIS',
        'SP' => 'ShopeePay',
        'OV' => 'OVO',
        'DA' => 'DANA',
        'LA' => 'Lazada Wallet',
        'LP' => 'LinkAja',

        // Convenience Store (Retail)
        'FT' => 'Pegadaian / POS',
        'AL' => 'Alfamart',
        'IN' => 'Indomaret',

        // Credit Card & Paylater
        'VC' => 'Credit Card (Visa/Master)',
        'MY' => 'Indodana Paylater',
        'AT' => 'Atome',
        'AK' => 'Akulaku Paylater',

        // Internet Banking / Direct Debit
        'BK' => 'BCA KlikPay', 
        'B2' => 'CIMB Clicks',
        'S1' => 'Sinarmas Danamas',
        'IB' => 'Muamalat Internet Banking',
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
    protected $casts = [
        'installment_ids'  => 'array',    // JSON MariaDB otomatis jadi Array PHP
        'callback_payload' => 'array',    // JSON MariaDB otomatis jadi Array PHP
        'paid_at'          => 'datetime', // Mengubah string tanggal menjadi objek Carbon
        'base_amount'      => 'integer',
        'admin_fee'        => 'integer',
        'total_amount'     => 'integer',
        'payment_method_code' => 'string',
    ];

    public function student()
    {
        // Asumsi model siswa Anda bernama Student
        return $this->belongsTo(Student::class, 'student_id');
    }

    // Relasi ke model Installment
    public function installments()
    {
        // Mencari semua installment yang ID-nya ada di dalam array installment_ids
        return Installment::whereIn('id', $this->installment_ids ?? []);
    }

    // Relasi ke model Contract melalui installment
    public function contracts()
    {
        // Ambil semua ID kontrak dari installment yang terkait
        $contractIds = Installment::whereIn('id', $this->installment_ids ?? [])
            ->pluck('installment_contract_id')
            ->unique();

        return Contract::whereIn('id', $contractIds)->withTrashed();
    }

    public function paymentUse()
    {
        return $this->hasMany(PaymentUse::class, 'payment_id')
                    ->where('payment_duitku', 1);
    }

    public function getContractDetailsAttribute()
    {
        return $this->contracts()
            ->with('contractLpk') // Pastikan relasi ini ada di model Contract
            ->get()
            ->map(fn($contract) => [
                'id' => $contract->id,
                'contractNo' => $contract->contract_no,
                'lpkId' => $contract->installment_contract_id,
                'contractLpk' => [
                    'id' => $contract->contractLpk->id ?? null,
                    'lpkName' => $contract->contractLpk->lpk_name ?? null,
                ]
            ]);
    }

    /**
     * Accessor untuk menampilkan nama metode di API
     */
    public function getPaymentMethodNameAttribute()
    {
        return self::$duitkuMapping[$this->payment_method_code] ?? $this->payment_method_code;
    }

}
