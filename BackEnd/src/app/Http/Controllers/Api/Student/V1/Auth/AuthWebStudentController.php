<?php

namespace App\Http\Controllers\Api\Student\V1\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Student\V1\Auth\LoginRequest;
use CloudCreativity\LaravelJsonApi\Document\Error\Error;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use IlluminateSupportFacadesLog; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;



class AuthWebStudentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param LoginRequest $request
     * @return mixed
     * @throws GuzzleException
     */
    public function __invoke(LoginRequest $request)
    {
        try {


            log::info($request);

            $validator = Validator::make($request->all(), [
                'data.attributes.email' => 'required|email',
                'data.attributes.password' => 'required|min:2',
                // 'data.attributes.fcmToken' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                return app(\LaravelJsonApi\Validation\Factory::class)
                    ->createErrors($validator);
            }
            
            $student = Student::whereStudentEmail($request->data['attributes']['email'])->first();

            if (!$student || ($student->studentLpk && $student->studentLpk->active == 0) || Hash::check($request->data['attributes']['password'], $student->password)!= 1) {
                $response = [
                    'jsonapi' => ['version'=>'1.0'],
                    'success' => false,
                    'errors' => ['detail'=>'The provided credentials are incorrect.','status'=> '422'],
                ];
                return response()->json($response, 422);
            }
            else{
                $success['token'] =  $student->createToken('MyApp')->plainTextToken; 
                $success['name'] =  $student->student_name;
                $success['email'] = $student->student_email;
                $success['loginType'] =  "default";
                if(isset($request->data['attributes']['fcmToken'])){
                 $student->fcm_token = $request->data['attributes']['fcmToken'];
                }
                $student->save();

                $response = [
                    'jsonapi' => ['version'=>'1.0'],
                    'success' => true,
                    'data'    => $success,
                    'message' => 'student login successfully',
                ];
                return response()->json($response, 200);
            }

        } catch (ClientException $e) {
            $error = json_decode((string)$e->getResponse()->getBody());
            $response = [
                'jsonapi' => ['version'=>'1.0'],
                'success' => false,
                'errors' => ['detail'=>$error->message,'status'=> '400'],
            ];
            return response()->json($response, 400);
        }
   

    }
}
