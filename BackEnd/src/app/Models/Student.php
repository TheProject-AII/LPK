<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Scopes\LpkScope;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Authenticatable implements HasMedia
{
    use  HasApiTokens, HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;

    public static function boot()
    {
        parent::boot();
        static::created(function ($item) {

            // $item->student_no = IdGenerator::generate(['table' => 'students', 'length' => 20, 'field' => 'student_no', 'prefix' => str_replace(' ','',$item->studentLpk->lpk_name). '/' . date('Y') . '/' . date('m') . '/', 'reset_on_prefix_change' => true]);
            // $item->save();

            $studentStatus = new StudentStatus;
            $studentStatus->student_id = $item->id;
            $studentStatus->status = 1;
            $studentStatus->status_by_id = Auth::id();
            $studentStatus->save();
        });
        static::creating(function ($item) {

            // $item->student_no = IdGenerator::generate(['table' => 'students', 'length' => 20, 'field' => 'student_no', 'prefix' => 'STUDENT/' . date('Y') . '/' . date('m') . '/', 'reset_on_prefix_change' => true]);
            $prefix = str_replace(' ','',$item->studentLpk->lpk_name). '/' . date('Y') . '/' . date('m') . '/';
            $length = strlen(str_replace(' ','',$item->studentLpk->lpk_name)) + 13;
            $item->student_no = IdGenerator::generate(['table' => 'students','field'=>'student_no', 'length' => $length, 'field' => 'student_no', 'prefix' =>  $prefix, 'reset_on_prefix_change' => true]);
        });
    }


    // protected $appends = [
    //     'student_attach_folder_url'
    // ];


    public function registerMediaCollections(): void
    {

        // ktpPemohonPenjamin
        // kartuKeluarga
        // ijazahSekolah
        // fotoKeluarga
        // kontrakPerjanjianHirePurchase
        // suratPermohonanHirePurchase
        // suratKesanggupanBayar
        // hasilPraMedicalCheckUp
        // nameTagLpk
        // certificateOfEligibility
        // surpervisingOrganization
        // kontrakKerjaDiJepang
        // tiketPesawatKeberangkatan
        // formulirPendaftaranKoperasi
        // suratPersetujuanOrangTua

        $this->addMediaCollection('studentAttachmentPhoto')->singleFile()->useDisk('student_attachment_photo');
        $this->addMediaCollection('ktpPemohonPenjamin')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('kartuKeluarga')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('ijazahSekolah')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('fotoKeluarga')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('kontrakPerjanjianHirePurchase')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('suratPermohonanHirePurchase')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('suratKesanggupanBayar')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('hasilPraMedicalCheckUp')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('nameTagLpk')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('certificateOfEligibility')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('surpervisingOrganization')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('kontrakKerjaDiJepang')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('tiketPesawatKeberangkatan')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('formulirPendaftaranKoperasi')->singleFile()->useDisk('student_required_docs');
        $this->addMediaCollection('suratPersetujuanOrangTua')->singleFile()->useDisk('student_required_docs');
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
        'lpk_id',
        'student_no',
        'student_name',
        'student_type',
        'student_address',
        'student_email',
        'student_place_of_birth_id',
        'student_date_of_birth',
        // 'student_type',
        'student_gender',
        'student_blood_type',
        'student_religion',
        'student_last_education',
        'student_phone_no',
        'student_kumiai_name',
        'student_kumiai_address',
        'student_employement_in_japan',
        'student_address_in_japan',
        'student_parent_name',
        'student_parent_relationship',
        'student_parent_phone_no',
        'student_work_program_in_japan',
        'student_company_name_in_japan',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

     protected $casts = [];


  public function studentIdentitys()
    {
        return $this->hasMany(StudentIdentity::class, 'student_id');
    }

    public function studentIdentityList()
    {
        return $this->hasMany(StudentIdentity::class, 'student_id')
        ->select('id','student_identity_type','student_identity_no','student_identity_name_of_educational_institution','student_identity_expire_date','student_identity_address','student_identity_place_of_issue');
    }

    // public function studentLicenses()
    // {
    //      return $this->hasMany(StudentLicense::class, 'student_id');
    // }

    public function historyStatus()
    {
         return $this->hasMany(StudentStatus::class,'student_id')->withTrashed()->orderBy('created_at');

     }

     public function currentStatus()
    {
         return $this->hasOne(StudentStatus::class,'student_id')->withTrashed()->latest()->select('status','status_remark');

     }

     public function lpkCategory()
    {
        return $this->belongsTo(LpkCategory::class, 'student_employement_in_japan');
    }




     public function studentBankAccounts()
    {
         return $this->hasMany(StudentBankAccount::class, 'student_id');
    }

    public function studentBankAccountList()
    {
         return $this->hasMany(StudentBankAccount::class, 'student_id')->select('id',
         'student_bank_account_bank_name',
         'student_bank_account_no',
         'student_bank_account_holder');
    }

    public function studentLpk()
    {
        return $this->belongsTo(Lpk::class, 'lpk_id')->withTrashed();
    }

     public function studentPlaceOfBirth()
    {
        return $this->belongsTo(City::class, 'student_place_of_birth_id')->withTrashed();
    }

    public function historyPayment()
        {
             return $this->hasMany(Payment::class,'payment_student_id');

         }

     public function studentEmployementInJapan()
    {
        return $this->belongsTo(LpkCategory::class, 'student_employement_in_japan')->withTrashed();
    }

    public function studentContractActive()
    {
       return $this->hasOne(Contract::class,'student_id')->latest();
    }

    public function studentContractActiveInstallmentActive()
    {
        // return $this->hasOneThrough(
        //     Installment::class,
        //     Contract::class,
        //     'student_id', // Foreign key on Contract table...
        //     'installment_contract_id', // Foreign key on Installment table...
        //     'id', // Local key on Student table...
        //     'id' // Local key on Contract table...
        // );

        return $this->through('studentContractActive')->has('installmentActive')->orderBy('installment_sequence_no', 'asc')->where('installments.status',"!=", 3);
    }

    public function studentNotification()
    {
        return $this->hasManyThrough(
            Notification::class,
            NotificationStudent::class,
            'student_id', // Foreign key on NotificationStudent table...
            'id', // Foreign key on Notification table...
            'id', // Local key on Student table...
            'notification_id' // Local key on NotificationStudent table...
        )->select('*','notifications.id');
    }



    /**
     * @return array
     */
    public function getStudentPhotoUrlAttribute()
    {
        $mediaItems = $this->getMedia("studentAttachmentPhoto");
        return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getktpPemohonPenjaminUrlAttribute()
    {
        $mediaItems = $this->getMedia("ktpPemohonPenjamin");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

    public function getijazahSekolahUrlAttribute()
    {
        $mediaItems = $this->getMedia("ijazahSekolah");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getkartuKeluargaUrlAttribute()
    {
        $mediaItems = $this->getMedia("kartuKeluarga");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getfotoKeluargaUrlAttribute()
    {
        $mediaItems = $this->getMedia("fotoKeluarga");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getkontrakPerjanjianHirePurchaseUrlAttribute()
    {
        $mediaItems = $this->getMedia("kontrakPerjanjianHirePurchase");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getsuratPermohonanHirePurchaseUrlAttribute()
    {
        $mediaItems = $this->getMedia("suratPermohonanHirePurchase");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getsuratKesanggupanBayarUrlAttribute()
    {
        $mediaItems = $this->getMedia("suratKesanggupanBayar");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function gethasilPraMedicalCheckUpUrlAttribute()
    {
        $mediaItems = $this->getMedia("hasilPraMedicalCheckUp");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getnameTagLpkUrlAttribute()
    {
        $mediaItems = $this->getMedia("nameTagLpk");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getcertificateOfEligibilityUrlAttribute()
    {
        $mediaItems = $this->getMedia("certificateOfEligibility");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getsurpervisingOrganizationUrlAttribute()
    {
        $mediaItems = $this->getMedia("surpervisingOrganization");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getkontrakKerjaDiJepangUrlAttribute()
    {
        $mediaItems = $this->getMedia("kontrakKerjaDiJepang");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function gettiketPesawatKeberangkatanUrlAttribute()
    {
        $mediaItems = $this->getMedia("tiketPesawatKeberangkatan");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getformulirPendaftaranKoperasiUrlAttribute()
    {
        $mediaItems = $this->getMedia("formulirPendaftaranKoperasi");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }

      /**
     * @return array
     */
    public function getsuratPersetujuanOrangTuaUrlAttribute()
    {
        $mediaItems = $this->getMedia("suratPersetujuanOrangTua");
         return count($mediaItems) != 0 ? $mediaItems[0]->getFullUrl() : null;
    }



}
