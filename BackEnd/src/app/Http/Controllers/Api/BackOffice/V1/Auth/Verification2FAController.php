<?php

namespace App\Http\Controllers\Api\BackOffice\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BackOffice\V1\Auth\Verification2FARequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class Verification2FAController extends Controller
{
    public function __invoke(Verification2FARequest $request)
    {
        $email = $request->data['attributes']['email'];
        $token = $request->data['attributes']['token'] ?? null;

        $user = User::whereEmail($email)
            ->where('active', 1)
            ->first();

        // Validasi user dan status LPK
        if (!$user || ($user->userLpk && $user->userLpk->active == 0)) {
            return response()->json([
                'jsonapi' => ['version' => '1.0'],
                'success' => false,
                'errors' => [
                    'detail' => 'The provided credentials are incorrect.',
                    'status' => '422',
                ],
            ], 422);
        }

        // Jika user memiliki Google Authenticator
        if ($user->google_two_fa_secret) {
            $google2fa = app('pragmarx.google2fa');

            $valid = $google2fa->verifyKey(
                $user->google_two_fa_secret,
                $token
            );

            if (!$valid) {
                return response()->json([
                    'jsonapi' => ['version' => '1.0'],
                    'success' => false,
                    'errors' => [
                        'detail' => 'Invalid Token',
                        'status' => '422',
                    ],
                ], 422);
            }
        } else {
            // Bypass hanya untuk akun testing di environment local
            if (
                !app()->environment('local') ||
                $user->email !== 'admin@lpk.test'
            ) {
                return response()->json([
                    'jsonapi' => ['version' => '1.0'],
                    'success' => false,
                    'errors' => [
                        'detail' => 'The provided credentials are incorrect.',
                        'status' => '422',
                    ],
                ], 422);
            }

            Log::info('Local testing account: 2FA bypassed');
        }

        // Generate token login
        $success = [
            'token' => $user->createToken('MyApp')->plainTextToken,
            'name' => $user->name,
            'email' => $user->email,
            'loginType' => 'default',
        ];

        return response()->json([
            'jsonapi' => ['version' => '1.0'],
            'success' => true,
            'data' => $success,
            'message' => 'User login successfully',
        ], 200);
    }
}
