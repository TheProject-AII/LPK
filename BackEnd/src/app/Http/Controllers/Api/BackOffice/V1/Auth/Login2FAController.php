<?php

namespace App\Http\Controllers\Api\BackOffice\V1\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use App\Http\Requests\Api\BackOffice\V1\Auth\LoginRequest;
use CloudCreativity\LaravelJsonApi\Document\Error\Error;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use IlluminateSupportFacadesLog; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;



class Login2FAController extends Controller
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
            // TODO: This should be removed in production since it is a security measure
            // Without this it crashes on local development
           
            // Log::info($request);

            // $validator = Validator::make($request->all(), [
            //     'data.attributes.email' => 'required|email|exists:users,email',
            //     'data.attributes.password' => 'required|min:2',
            // ]);
    
            // if ($validator->fails()) {
            //     return app(\LaravelJsonApi\Validation\Factory::class)
            //         ->createErrors($validator);
            // }


            // $login = $request->validate([
            //     'data.attributes.email' => 'required|email|exists:users,email',
            //     'data.attributes.password' => 'required|string',
            // ]);
           
                $user = User::whereEmail($request->data['attributes']['email'])
                ->where('active',1)->first();
                
                //with active fiture
                // $user = User::whereEmail($request->data['attributes']['email'])->where(active,1)->first();

                if (!$user || ($user->userLpk && $user->userLpk->active == 0) || !Hash::check($request->data['attributes']['password'], $user->password)) {
                    $response = [
                        'jsonapi' => ['version'=>'1.0'],
                        'success' => false,
                        'errors' => ['detail'=>'The provided credentials are incorrect.','status'=> '422'],
                    ];
                    Log::info('Response being returned:', $response);
                    return response()->json($response, 422);
                } else {

                    
                   if ($user->google_two_fa_secret) {
    $code = 200;
    $data = [];
} else {
    if (
        app()->environment('local') &&
        $user->email === 'admin@lpk.test'
    ) {
        // Akun testing tidak perlu registrasi Google Authenticator
        $code = 200;
        $data = [];
    } else {
        $google2fa = app('pragmarx.google2fa');

        $registration_data = $request->data['attributes'];
        $registration_data['google2fa_secret'] = $google2fa->generateSecretKey();

        $user->google_two_fa_secret = $registration_data['google2fa_secret'];
        $user->save();

        $QR_Image = $google2fa->getQRCodeInline(
            config('app.name'),
            $registration_data['email'],
            $registration_data['google2fa_secret']
        );

        $code = 200;
        $data = [
            'secret' => $registration_data['google2fa_secret'],
            'qr' => str_replace(
                ["\n\r", "\n", "\r"],
                '',
                str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $QR_Image)
            ),
        ];
    }
}

                    
                    


                }
          
            return response()->json($data, $code);
            // return response()->json(array('errors' => [array('status' => $status, 'title' =>'Have an Error''data' => $data)]), $status);


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
