<?php

namespace App\Http\Controllers\Api\BackOffice\V1;
use App\Http\Controllers\Controller;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use App\Models\InstallmentChanges;
use App\Models\Installment;
use App\Models\PaymentPlans;
use App\Models\Contracts;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use CloudCreativity\LaravelJsonApi\Document\Error\Error;
use CloudCreativity\LaravelJsonApi\Http\Controllers\JsonApiController;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class InstallmentController extends JsonApiController
{

    private function getWeekInstallment($list_installment,$LastInstallment){

        foreach ($list_installment as $installment) {
            //cari instalmen/divider berdasarkan nilai last spj sequance no lebih besar dari installment week
           if($LastInstallment->installment_sequence_no >= $installment->payment_plan_installment_week){
               $payment_plan_installment_amount = $installment->payment_plan_installment_amount;
           }

         }
         return $payment_plan_installment_amount;
    }

}
