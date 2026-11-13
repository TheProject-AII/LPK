<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use App\Models\Scopes\LpkScope;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Contract extends Model implements HasMedia
{
    use  InteractsWithMedia, HasFactory, HasUuids, SoftDeletes;

    // protected static function booted()
    // {
    //     static::addGlobalScope(new LpkScope);
    // }

    public static function boot() {


	    parent::boot();


	    static::created(function($item) {

            $contractStatus = new ContractStatus;
            $contractStatus->contract_id = $item->id;
            $contractStatus->status = 1;
            $contractStatus->status_by_id = auth()->user()->id;
            $contractStatus->save();

            // $studentStatus = new StudentStatus;
            // $studentStatus->student_id = $item->student_id;
            // $studentStatus->status = 7;
            // $studentStatus->status_by_id = Auth::id();
            // $studentStatus->save();

	    });

	    static::creating(function($item) {

            $item->contract_no = IdGenerator::generate(['table' => 'contracts', 'length' => 21, 'field' => 'contract_no', 'prefix' => 'CONTRACT/'.date('Y').'/'.date('m').'/', 'reset_on_prefix_change' => true]);
	    });



	// }
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
        'student_id',
        'lpk_id',
        'contract_no',
        'contract_remark',
        'contract_payment_plan_id',
        // 'contract_start_date',
        'contract_end_date',
        'contract_date_of_departure',
        'contract_initial_contract_in_japan',
        'contract_end_of_contract_in_japan',
        'contract_payday',
        'contract_process_fee',
        'contract_membership',
        'contract_provision_fee_enabled',
        'contract_tax_enabled',
        'contract_tax_rate',
        'contract_principle',
        'contract_total_payable',
        'contract_grace_period',
        'contract_disbursment_date',
        'contract_wage_daily',
        'contract_cut_off_date',
        'contract_days_off',
        'contract_kurs_yen',
        'contract_provision_already_paid',
        'contract_student_need_training',
        'contract_first_working_day',
    ];

    protected $appends = ['contract_remaining_payment','contract_paid_payment','contract_total_penalty', 'contract_total_installment'];

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
    protected $casts = [
        'contract_days_off' => 'array',
        'contract_provision_fee_enabled' => 'boolean',
        'contract_tax_enabled' => 'boolean',
    ];

    public function contractStudent()
    {
         return $this->belongsTo(Student::class, 'student_id')->withTrashed()->with('studentIdentitys','studentPlaceOfBirth');
    }

    public function contractInstallment()
    {
         return $this->hasMany(Installment::class,'installment_contract_id')->withTrashed()->orderBy('installment_sequence_no');

     }

    public function contractLpk()
    {
         return $this->belongsTo(Lpk::class, 'lpk_id')->withTrashed();
    }

    public function contractPaymentPlan()
    {
         return $this->belongsTo(PaymentPlan::class, 'contract_payment_plan_id')->withTrashed();
    }

    public function currentStatus()
    {
         return $this->hasOne(ContractStatus::class,'contract_id')->withTrashed()->latest()->select('status');
     }


    public function historyStatus()
    {
         return $this->hasMany(ContractStatus::class,'contract_id')->orderBy('created_at');

    }

    public function historyPayment()
        {
             return $this->hasMany(Payment::class,'payment_contract_id');

         }


      /**
     * @return array
     */
    public function getcontractAttachmentFormulirPendaftaranAnggotaBaruUrlAttribute()
    {
        $mediaItems = $this->getMedia("contractAttachmentFormulirPendaftaranAnggotaBaru");
        return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getcontractAttachmentSuratPermohonanSewaBeliUrlAttribute()
    {
        $mediaItems = $this->getMedia("contractAttachmentSuratPermohonanSewaBeli");
        // Log::info($mediaItems);
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getcontractAttachmentSuratPernyataanKesanggupanMembayarUrlAttribute()
    {
        $mediaItems = $this->getMedia("contractAttachmentSuratPernyataanKesanggupanMembayar");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getcontractAttachmentPerjanjianSewaBeliUrlAttribute()
    {
        $mediaItems = $this->getMedia("contractAttachmentPerjanjianSewaBeli");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getcontractAttachmentSuratPernyataanDanJaminanUrlAttribute()
    {
        $mediaItems = $this->getMedia("contractAttachmentSuratPernyataanDanJaminan");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    // public function getcontractAttachmentSuratPernyataanPersetujuaanOrangTuaWaliUrlAttribute()
    // {
    //     $mediaItems = $this->getMedia("contractAttachmentSuratPernyataanPersetujuaanOrangTuaWali");
    //      return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    // }

      /**
     * @return array
     */
    public function getcontractAttachmentTandaTerimaPenyerahanIjasahUrlAttribute()
    {
        $mediaItems = $this->getMedia("contractAttachmentTandaTerimaPenyerahanIjasah");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('contractAttachmentFormulirPendaftaranAnggotaBaru')->singleFile()->useDisk('contract_attachment');
        $this->addMediaCollection('contractAttachmentSuratPermohonanSewaBeli')->singleFile()->useDisk('contract_attachment');
        $this->addMediaCollection('contractAttachmentSuratPernyataanKesanggupanMembayar')->singleFile()->useDisk('contract_attachment');
        $this->addMediaCollection('contractAttachmentPerjanjianSewaBeli')->singleFile()->useDisk('contract_attachment');
        $this->addMediaCollection('contractAttachmentSuratPernyataanDanJaminan')->singleFile()->useDisk('contract_attachment');
        $this->addMediaCollection('contractAttachmentSuratPernyataanPersetujuaanOrangTuaWali')->singleFile()->useDisk('contract_attachment');
        $this->addMediaCollection('contractAttachmentTandaTerimaPenyerahanIjasah')->singleFile()->useDisk('contract_attachment');
    }

    public function installmentLast()
    {
         return $this->hasOne(Installment::class,'installment_contract_id')->orderBy('installment_sequence_no', 'desc');

    }

    public function installmentActiveAll()
    {
         return $this->hasMany(Installment::class,'installment_contract_id')->orderBy('installment_sequence_no', 'asc')->where('status',"!=", 3);

     }

     public function installmentPaid()
    {
         return $this->hasMany(Installment::class,'installment_contract_id')->orderBy('installment_sequence_no', 'asc')->where('status',"!=", 1);

     }


    public function installmentActive()
    {
         //get last by installment status
         return $this->hasOne(Installment::class,'installment_contract_id')->orderBy('installment_sequence_no', 'asc')->where('status',"!=", 3);

        //get last status from history installment status
        //  return $this->hasOne(Installment::class,'installment_contract_id')->orderBy('installment_sequence_no', 'asc')->whereHas('currentStatus',fn(EloquentBuilder $query) =>
        //  $query ->where('status',"!=", 2)
        //  ->orderBy('installment_id', 'desc')
        //  ->whereIn('id', function (QueryBuilder $query) {
        //      $query
        //          ->select('id')
        //          ->from('installment_statuses')
        //          ->whereColumn('installment_id', 'installments.id')
        //          ->orderBy('created_at');
        //  })
        //  );

    }


    public function getContractRemainingPaymentAttribute()
        {
            $installment = $this->contractInstallment()->get();

            $data = json_decode($installment, true);

            // $totalRemainingPayment = 0;
            // foreach ($data as $item) {
            //     $totalRemainingPayment += $item['remaining_payment'];
            // }

            $totalRemainingPayment = array_reduce($data, function ($carry, $item) {
                return $carry + $item['remaining_payment'];
            }, 0);

                return (int)$totalRemainingPayment;
        }

    public function getContractPaidPaymentAttribute()
        {
            $installment = $this->contractInstallment()->get();

            $data = json_decode($installment, true);



            $totalPaidPayment = array_reduce($data, function ($carry, $item) {
                return $carry + $item['payment_amount'];
            }, 0);

                return (int)$totalPaidPayment;
        }

    public function getContractTotalPenaltyAttribute()
        {
            $installment = $this->contractInstallment()->get();

            $data = json_decode($installment, true);



            $contractTotalPenalty = array_reduce($data, function ($carry, $item) {
                return $carry + $item['installment_penalty'];
            }, 0);

                return (int)$contractTotalPenalty;
        }

        public function getContractTotalInstallmentAttribute()
        {
            $installment = $this->contractInstallment()->get();

            $data = json_decode($installment, true);



            $installmentMonthlyInstallment = array_reduce($data, function ($carry, $item) {
                return $carry + $item['installment_monthly_installment'];
            }, 0);


                return (int)$installmentMonthlyInstallment;
        }

    public function getContractTotalPayableAttribute()
        {
            if($this->status == 4 || $this->status == 5){
                $installment = $this->contractInstallment()->get();

                $data = json_decode($installment, true);



                $installmentMonthlyInstallment = array_reduce($data, function ($carry, $item) {
                    return $carry + $item['installment_monthly_installment'];
                }, 0);

                $contractTotalPayable = $this->contract_total_installment + $this->contract_total_penalty;

                    return (int)$contractTotalPayable;
                }
                else{
                    // Return the current value of contract_total_payable
                    return $this->attributes['contract_total_payable'];
                }

        }






    // public function contractRemainingPayment()
    //     {
    //         $remainingPayment = $this->contractInstallment()->get();

    //         $data = json_decode($remainingPayment, true);

    //         // $totalRemainingPayment = 0;
    //         // foreach ($data as $item) {
    //         //     $totalRemainingPayment += $item['remaining_payment'];
    //         // }

    //         $totalRemainingPayment = array_reduce($data, function ($carry, $item) {
    //             return $carry + $item['remaining_payment'];
    //         }, 0);

    //             return (int)$totalRemainingPayment;
    //     }



}
