<?php

namespace App\JsonApi\BackOffice\V1\Users;

use Illuminate\Http\Request;
use LaravelJsonApi\Core\Resources\JsonApiResource;

class UserResource extends JsonApiResource
{

    // /**
    //  * Get the resource's attributes.
    //  *
    //  * @param Request|null $request
    //  * @return iterable
    //  */
    // public function attributes($request): iterable
    // {
    //     return [
    //         'name' => $this->name,
    //         'email' => $this->email,
    //         'roles' => $this->roles,
    //         'permissions' => $this->permissions,
    //         'createdAt' => $this->created_at,
    //         'updatedAt' => $this->updated_at,
    //     ];
    // }

    /**
     * Get the resource's relationships.
     *
     * @param Request|null $request
     * @return iterable
     */
    public function relationships($request): iterable
    {
        return [
            // @TODO
        ];
    }


    // public function meta($request): iterable
    // {
    //     if ($request->method() === 'POST') {
    //         return [
    //             'token' => $this->createToken('app')->plainTextToken,
    //         ];
    //     }
    //     return [];
    // }

}
