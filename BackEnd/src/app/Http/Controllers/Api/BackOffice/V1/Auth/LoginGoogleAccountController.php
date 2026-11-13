<?php

namespace App\Http\Controllers\Api\BackOffice\V1\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use CloudCreativity\LaravelJsonApi\Document\Error\Error;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use IlluminateSupportFacadesLog; 
use Illuminate\Support\Facades\Log;
use Google\Auth\AccessToken;



class LoginGoogleAccountController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return mixed
     * @throws GuzzleException
     */
    public function __invoke(Request $request)
    {
        try {
            // TODO: This should be removed in production since it is a security measure
            // Without this it crashes on local development
           
            // Log::info($request);

            $login = $request->validate([
                'credential' => 'required',
            ]);
           
            $auth = new AccessToken();
            
           $result = $auth->verify($login['credential']['credential']);
           

            // Log::info($result);
            if($result){
                $finduser = User::where('email', $result['email'])->first();
                if($finduser){
                    $success['token'] =  $finduser->createToken('MyApp')->plainTextToken; 
                    $success['name'] =  $finduser->name;
                    $success['email'] =  $finduser->email;
                    $success['loginType'] =  "googleAccount";
        

                    $response = [
                        'jsonapi' => ['version'=>'1.0'],
                        'success' => true,
                        'data'    => $success,
                        'message' => 'User login successfully',
                    ];
            
            
                    return response()->json($response, 200);

                }
                else{
                    $response = [
                        'jsonapi' => ['version'=>'1.0'],
                        'success' => false,
                        'errors' => ['email'=>$result['email'], 'detail'=>'The provided google account are incorrect.','status'=> '422'],
                    ];
                    return response()->json($response, 422);

                }
            }
            else{
                
                $response = [
                    'jsonapi' => ['version'=>'1.0'],
                    'success' => false,
                    'errors' => ['detail'=>'The provided credentials are incorrect.','status'=> '422'],
                ];
                return response()->json($response, 422);
            }



           
                $user = User::whereEmail($login['email'])->first();

                
                if (!$user || !Hash::check($login['password'], $user->password) === 1) {
                    
                } else {

                    
                    if($user->google_two_fa_secret){
                        
                        $code = 200;
                        $data = [];
                    }
                    else{
                        $google2fa = app('pragmarx.google2fa');
            
                        $registration_data = $request->all();
                
                        $registration_data["google2fa_secret"] = $google2fa->generateSecretKey();
                        $user->google_two_fa_secret = $registration_data["google2fa_secret"];
                        $user->save();

                        $QR_Image = $google2fa->getQRCodeInline(
                
                            config('app.name'),
                
                            $registration_data['email'],
                
                            $registration_data['google2fa_secret']
                
                        );
                        $code = 200;
                        $data = [
                            'secret' => $registration_data["google2fa_secret"],
                            'qr' => str_replace(["\n\r", "\n", "\r"], '', str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $QR_Image)),
                        ];
                        Log::info(str_replace(["\n\r", "\n", "\r"], '', str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $QR_Image)));

                        
                    }

                    
                    


                }
          
            return response()->json($data, $code);
            // return response()->json(array('errors' => [array('status' => $status, 'title' =>'Have an Error''data' => $data)]), $status);


        } catch (ClientException $e) {
            $error = json_decode((string)$e->getResponse()->getBody());

            return $this->reply()->errors([
                Error::fromArray([
                    'title' => 'Bad Request',
                    'detail' => $error->message,
                    'status' => '400',
                ])
            ]);
        }
   

    }
}
