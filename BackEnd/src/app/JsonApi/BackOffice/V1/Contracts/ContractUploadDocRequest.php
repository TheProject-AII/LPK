<?php

namespace App\JsonApi\BackOffice\V1\Contracts;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;
use Illuminate\Support\Facades\Log;

class ContractUploadDocRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        $docType = 'base64mimes:pdf,jpeg,png,jpg,gif,webp';

        return [
            'contractAttachmentPerjanjianSewaBeli' => [$docType,'base64max:2048'],
            'contractAttachmentFormulirPendaftaranAnggotaBaru' => [$docType,'base64max:2048'],
            'contractAttachmentSuratPermohonanSewaBeli' => [$docType,'base64max:2048'],
            'contractAttachmentSuratPernyataanKesanggupanMembayar' => [$docType,'base64max:2048'],
            'contractAttachmentSuratPernyataanDanJaminan' => [$docType,'base64max:2048'],
            'contractAttachmentTandaTerimaPenyerahanIjasah' => [$docType,'base64max:2048'],
        ];
    }

}
