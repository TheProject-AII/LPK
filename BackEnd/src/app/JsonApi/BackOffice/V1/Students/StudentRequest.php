<?php

namespace App\JsonApi\BackOffice\V1\Students;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class StudentRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $data = $this->model();
        $uniquePhoneNo = Rule::unique('students', 'student_phone_no');
        $required =  'required';

        if ($data) {
            $uniquePhoneNo->ignoreModel($data);
            $required =  '';
        }




        if(auth()->user()->lpk_id){

            $contractLpk = [];
         }
         else{
            $existLpk = Rule::exists('lpks','id');
            $contractLpk =  ['required', 'uuid', $existLpk];
         }



        return [
            'password' => [$required, 'string','same:passwordConfirmation'],
            'passwordConfirmation' => [$required, 'string'],
            'studentLpk' =>  $contractLpk,
            'studentName' => ['required', 'string'],
            'studentType' => ['required', 'string'],
            // 'studentAddress' => [ 'string'],
            'studentEmail' => ['required', 'email'],
            // 'studentDateOfBirth' => ['date_format:Y-m-d'],
            // 'studentType' => ['required', 'string'],
            // 'studentGender' => [ 'string'],
            // 'studentBloodType' => [ 'string'],
            // 'studentReligion' => [ 'string'],
            // 'studentLastEducation' => [ 'string'],
            'studentPhoneNo' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/' ,'min:10',$uniquePhoneNo],
            'studentEmergencyPhoneNo' => ['regex:/^([0-9\s\-\+\(\)]*)$/' ,'min:10','different:studentPhoneNo'],
            // 'studentPlaceOfBirth' => ['string'],
            'studentPhoto' => ['base64mimes:pdf,jpeg,png,jpg,gif,webp','base64max:2048'],
            'studentIdentitys' => [ 'array'],
            'studentIdentitys.*.studentIdentityType' => ['required'],
            'studentIdentitys.*.studentIdentityNo' => ['required', 'string'],
            'studentIdentitys.*.studentIdentityNameOfEducationalInstitution' => ['nullable', 'string'],
            'studentIdentitys.*.studentIdentityExpireDate' => ['nullable','date_format:Y-m-d'],
            'studentIdentitys.*.studentIdentityAddress' => ['nullable','string'],
            'studentIdentitys.*.studentIdentityPlaceOfIssue' => ['nullable','string'],
            'studentIdentitys.*.studentIdentityFile' => ['nullable','base64mimes:pdf,jpeg,png,jpg,gif,webp','base64max:4048'],
            // 'studentLicenses' => ['required', 'array'],
            // 'studentLicenses.*.studentLicenseType' => ['required', 'int'],
            // 'studentLicenses.*.studentLicenseNo' => ['required', 'string'],
            // 'studentLicenses.*.studentLicenseExpireDate' => ['date_format:Y-m-d'],
            // 'studentLicenses.*.studentLicenseAddress' => [ 'string'],
            // 'studentLicenses.*.studentLicensePlaceOfIssue' => ['string'],
            // 'studentLicenses.*.studentLicenseFile' => ['base64mimes:pdf,jpeg,png,jpg,gif','base64max:4048'],
            'studentBankAccounts' => ['array'],
            'studentBankAccounts.*.studentBankAccountBankName' => ['required','string'],
            'studentBankAccounts.*.studentBankAccountNo' => ['required', 'string', 'regex:/^\d+$/'],
            'studentBankAccounts.*.studentBankAccountHolder' => ['required','string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'studentIdentitys.*.studentIdentityType.required' => 'The student identity type field in student identity field is required.',
            'studentIdentitys.*.studentIdentityNo.required' => 'The student identity no field in student identity field is required.',
            'studentIdentitys.*.studentIdentityExpireDate.required' => 'The student identity expire date field in student identity field is required.',
            'studentIdentitys.*.studentIdentityExpireDate.date_format' => 'The student identity expire date field in student identity field must match the format Y-m-d.',
            'studentIdentitys.*.studentIdentityAddress.required' => 'The student identity address field in student identity field is required.',
            'studentIdentitys.*.studentIdentityPlaceOfIssue.required' => 'The student identity place of issue field in student identity field is required.',
            'studentBankAccounts.*.studentBankAccountBankName.required' => 'The student bank account bank name field in student bank account field is required.',
            'studentBankAccounts.*.studentBankAccountNo.required' => 'The student bank account no field in student bank account field is required.',
            'studentBankAccounts.*.studentBankAccountHolder.required' => 'The student bank account Holder field in student bank account field is required.',

        ];
    }

}
