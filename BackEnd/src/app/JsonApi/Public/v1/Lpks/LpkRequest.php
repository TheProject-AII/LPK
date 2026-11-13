<?php

namespace App\JsonApi\Public\v1\Lpks;

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

        if ($data) {
            $uniqueLpkName->ignoreModel($data);
        }

        return [
            'lpkTitle' => ['required', 'string'],
            'lpkName' => ['required', 'string', $uniqueLpkName],
        ];
    }

}
