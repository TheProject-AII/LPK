<?php

namespace App\JsonApi\BackOffice\V1\Notifications;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class NotificationRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        // $data = $this->model();
        // $uniqueCategoryName = Rule::unique('categories', 'category_name');

        // if ($data) {
        //     $uniqueCategoryName->ignoreModel($data);
        // }

        return [
             'notificationType' => ['required', 'int'],
             'notificationAt' => ['required', 'date_format:Y-m-d H:i'],
             'notificationSubject' => ['required', 'string'],
             'notificationInformation' => ['required', 'string'],
             'notificationImage' => ['base64mimes:pdf,jpeg,png,jpg,gif,webp,zip,rar','base64max:1000'],
             'notificationContent' => ['required', 'json'],
             'notificationLpk' => ['required', 'uuid','exists:lpks,id'],
             'notificationStudent' => ['required', 'array'],
             'notificationStudent.*' => ['uuid','distinct','exists:students,id'],
             'notificationAttachment' => ['array'],
             'notificationAttachment.*.id' => ['uuid'],
             'notificationAttachment.*.base64' => ['required_without:notificationAttachment.*.id','base64mimes:pdf,jpeg,png,jpg,gif,webp,zip,rar','base64max:3000'],
             'notificationAttachment.*.name' => ['required_without:notificationAttachment.*.id', 'string'],
        ];
    }

}
