<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Class AuthController
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @param  Request  $request
     * @return string|null
     */
    public function registerUser(Request $request)
    {
        //TODO Check if user already registered, then redirect to getuserToken
        $fields = $request->validate(
            [
                'device_name' => 'required|string',
                'email'       => 'required|email',
                'password'    => 'required|string'
            ]
        );

        $api_user = User::create(
            [
                'name'      => $fields['device_name'],
                'email'     => $fields['email'],
                'password'  => Hash::make($fields['password']),
                'is_active' => true
            ]
        );

        $result = [
            'data' => [
                'user' => $api_user,
            ]
        ];

        return response()->json($result, 201);
    }

    /**
     * @param  Request  $request
     * @throws ValidationException
     */
    public function getUserToken(Request $request)
    {
        $fields = $request->validate(
            [
                'device_name' => 'required|string',
                'email'       => 'required|email',
                'password'    => 'required|string'
            ]
        );

        $api_user = User::where('email', $fields['email'])->first();

        if (!$api_user || !$api_user->is_active) {
            $result = [
                'data' => [
                    'error' => 'The provided credentials are incorrect'
                ]
            ];

            return response()->json($result, 201);
        } else {
            if ($existing_token = $api_user->tokens()->where('name', $fields['device_name'])->first()) {
                $existing_token->delete();
            }

            $result = [
                'data' => [
                    'token' => $api_user->createToken($fields['device_name'])->plainTextToken
                ]
            ];

            return response()->json($result, 201);
        }
    }

    /**
     * @param  Request  $request
     */
    public function logout(Request $request)
    {
        $user_data = $request->user();

        $user_data->tokens->where('name', $user_data['name'])->first()->delete();

        $result = [
            'data' => [
                'message' => 'Logout success. Current User token destroyed.'
            ]
        ];

        return response()->json($result, 201);
    }
}
