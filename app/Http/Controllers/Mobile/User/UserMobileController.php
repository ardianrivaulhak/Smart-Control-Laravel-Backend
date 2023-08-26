<?php

namespace App\Http\Controllers\Mobile\User;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserMobileController extends Controller
{
    //
    public function index()
    {
        $user = JWTAuth::user();
        $user['roleName'] = $user->role->name;

        return response()->json($user);
    }

    public function updatePassword(Request $request)
    {
        $user = JWTAuth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6',
            'confirm_new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(["message" => "Current password is incorrect"], 401);
        }

        if ($request->new_password != $request->confirm_new_password) {
            return response()->json(["message" => "New password and confirm new password does not match"], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully',
            'user' => $user,
        ]);
    }

    public function updatePhoto(Request $request)
    {

        $user = JWTAuth::user();
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');

        $fileName = time() . '-' .  $file->getClientOriginalName();

        $filePath = $file->storeAs('public/uploads', $fileName);
        $filePath = str_replace('public', 'storage', $filePath);

        $user->photo_url = $filePath;
        $user->save();

        return response()->json([
            'message' => 'User image upload successfully',
        ]);
    }
}
