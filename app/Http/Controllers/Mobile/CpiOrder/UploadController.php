<?php

namespace App\Http\Controllers\Mobile\CpiOrder;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    //
    public function store(Request $request)
    {
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

        $filePath = $file->storeAs('public/temps', $fileName);
        $filePath = str_replace('public', 'storage', $filePath);

        return response()->json([
            'message' => 'File uploaded sucessfully',
            'file_path' => $filePath,
        ]);
    }
}
