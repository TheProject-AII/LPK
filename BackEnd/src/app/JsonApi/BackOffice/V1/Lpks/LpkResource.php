<?php

namespace App\JsonApi\BackOffice\V1\Citys;

use Illuminate\Http\Request;
use LaravelJsonApi\Core\Resources\JsonApiResource;

class CityResource extends JsonApiResource
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
    //         'cityName' => $this->city_name,
    //         'cityProvince' => $this->city_province,
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

}
