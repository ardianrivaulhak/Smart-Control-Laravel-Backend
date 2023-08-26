<?php

namespace App\Http\Controllers\Mobile\CpiOrder;


use App\Models\Document;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class DocumentController extends Controller
{
    //
    public function index()
    {
        $document = Document::all();
        return response()->json([
            'message' => 'success',
            'data' => $document
        ]);
    }
}
