<?php

namespace App\JsonApi\BackOffice\V1\Roles;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class RoleRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $data = $this->model();
        $uniqueRoleName = Rule::unique('roles', 'name');

        if ($data) {
            $uniqueRoleName->ignoreModel($data);
        }

        if(auth()->user()->lpk_id){

            $contractLpk = [];
         }
         else{
            $existLpk = Rule::exists('lpks','id');
            $contractLpk =  [ 'uuid', $existLpk];
         }

        return [
            'name' => ['required', 'string', $uniqueRoleName],
            // 'roleLpk' =>  $contractLpk,
            'permissions' => ['array']
        ];
    }

}
