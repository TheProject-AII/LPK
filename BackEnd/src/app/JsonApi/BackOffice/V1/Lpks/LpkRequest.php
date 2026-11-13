<?php

namespace App\JsonApi\BackOffice\V1\Lpks;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class LpkRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {

        $data = $this->model();
        $uniqueLpkName = Rule::unique('lpks', 'lpk_name');
        $uniqueLpkEmail = Rule::unique('lpks', 'lpk_email');
        $required =  'required';

        if ($data) {
            $uniqueLpkName->ignoreModel($data);
            $uniqueLpkEmail->ignoreModel($data);
            $uniqueLpkEmail->ignoreModel($data);
            $required =  '';
        }

        return [
            'password' => [$required, 'string','same:passwordConfirmation'],
            'passwordConfirmation' => [$required, 'string'],
            'lpkName' => ['required', 'string', $uniqueLpkName],
            'lpkEmail' => ['required', 'string', $uniqueLpkEmail],
            'lpkBankAccountNumber' => ['required', 'string', 'regex:/^\d+$/'],
            'lpkBankAccountName' => ['required', 'string'],
            'lpkBankBranch' => ['required', 'string'],
            'lpkContactPersonIdentityNo' => ['required', 'string'],
            'lpkContactPersonPassportNo' => ['required', 'string'],
            'lpkContactPersonNationality' => ['required', 'string'],
            'lpkContactPersonOccupation' => ['required', 'string'],
            'lpkContactPersonAddress' => ['required', 'string'],
            'lpkContactPersonDateOfBirth' => ['required', 'string'],
            'lpkContactPersonPlaceOfBirth' => ['required', 'string'],
            'lpkContactPersonPhoneNo' => ['required', 'string'],
            'lpkContactPersonName' => ['required', 'string'],

        ];


    }

    public function messages()
        {
            return [
                'lpkName.unique' => 'LPK Name already exist',
                'lpkEmail.unique' => 'LPK Email already exist',

            ];
        }

}
