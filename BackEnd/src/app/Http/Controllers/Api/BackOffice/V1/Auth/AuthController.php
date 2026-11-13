<?php

namespace App\Http\Controllers\Api\BackOffice\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Core\Responses\MetaResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    // public function login(Request $request)
    // {
    //     Log::info($request);
    //     $validator = Validator::make($request->all(), [
    //         'data.attributes.email' => 'required|email',
    //         'data.attributes.password' => 'required|min:2',
    //     ]);

    //     if ($validator->fails()) {
    //         return app(\LaravelJsonApi\Validation\Factory::class)
    //             ->createErrors($validator);
    //     }

    //     $user = User::where('email', $request->input('data.attributes.email'))->first();

    //     if (! $user || Hash::check($request->input('data.attributes.password'), $user->password)!== 1) {
    //         throw JsonApiException::error([
    //             'status' => 400,
    //             'detail' => 'The provided credentials are incorrect.',
    //         ]);
    //     }

    //     return DataResponse::make($user)->withServer('v1');
    // }


     /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
                    'data.attributes.email' => 'required|email',
                    'data.attributes.password' => 'required|min:2',
                ]);
        
                if ($validator->fails()) {
                    return app(\LaravelJsonApi\Validation\Factory::class)
                        ->createErrors($validator);
                }
                
                $user = User::whereEmail($request->data['attributes']['email'])->first();

                if (!$user || !Hash::check($request->data['attributes']['password'], $user->password)) {
                    $response = [
                        'jsonapi' => ['version'=>'1.0'],
                        'success' => false,
                        'errors' => ['detail'=>'The provided credentials are incorrect.','status'=> '422'],
                    ];
                    return response()->json($response, 422);
                }
                else{
                    $success['token'] =  $user->createToken('MyApp')->plainTextToken; 
                    $success['name'] =  $user->name;
                    $success['email'] =  $user->email;
                    $success['loginType'] =  "default";
        

                    $response = [
                        'jsonapi' => ['version'=>'1.0'],
                        'success' => true,
                        'data'    => $success,
                        'message' => 'User login successfully',
                    ];
                    return response()->json($response, 200);
                }
    }
}
