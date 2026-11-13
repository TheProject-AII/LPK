<?php

namespace App\JsonApi\BackOffice\V1\Categories;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class CategoryRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $data = $this->model();
        $uniqueCategoryName = Rule::unique('categories', 'category_name');

        if ($data) {
            $uniqueCategoryName->ignoreModel($data);
        }

        return [
             'categoryName' => ['required', 'string', $uniqueCategoryName],
        ];
    }

}
