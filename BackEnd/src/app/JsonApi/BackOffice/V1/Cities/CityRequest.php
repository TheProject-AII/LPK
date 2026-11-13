<?php

namespace App\JsonApi\BackOffice\V1\Cities;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class CityRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $data = $this->model();
        $uniqueCityName = Rule::unique('cities', 'city_name');

        if ($data) {
            $uniqueCityName->ignoreModel($data);
        }

        return [
             'cityName' => ['required', 'string', $uniqueCityName],
        ];
    }

}
