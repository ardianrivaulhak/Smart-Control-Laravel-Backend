<?php

namespace App\Http\Controllers\Web\Login;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'npk' => 'required|numeric|digits:5',
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

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
        ]);
    }



    public function me(Request $request)
    {
        $user = JWTAuth::user();
        $token = $request->bearerToken();
        $user->load(['role.accesses' => function ($query) {
            $query->select('accesses.id', 'accesses.name')->distinct();
        }, 'role.accesses.permissions' => function ($query) {
            $query->select('permissions.id', 'permissions.name')->withPivot('status', 'is_disable');
        }]);

        return response()->json([
            'message' => 'Successfully get data user with token',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }
}
