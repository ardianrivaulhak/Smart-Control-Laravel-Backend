<?php

namespace App\Http\Controllers\Mobile\Login;

use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthMobileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'npk' => 'required|string',
                'password' => 'required|string|min:6',
            ]
        );


        if ($validator->fails()) {
            # code...
            return response()->json([$validator->errors()->toJson()], 401);
        }

        if (!$token = auth()->attempt($validator->validate())) {
            # code...
            return response()->json([
                'status' => 'error',
                'message' => 'Npk Or Password Incorect',
            ], 401);
        }

        return $this->createNewToken($token);
    }

    public function createNewToken($token)
    {
        $user = auth()->user();
        $user->role;
        $user->streams;

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
        ]);
    }
}
