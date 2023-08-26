<?php

namespace App\Http\Controllers\Web\User;

use App\Models\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AccessController extends Controller
{
    //

    public function index(Request $request)
    {

        $search = $request->input('search', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $access = Access::where('name', 'ilike', '%' . $search . '%')
            ->paginate($limit, ['id', 'name',]);

        $tableData = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'data' => $access->items(),
            'totalRows' => $access->total(),
            'totalPages' => $access->lastPage(),
            'nextPage' => $access->nextPageUrl(),
            'prevPage' => $access->previousPageUrl(),
        ];

        return response()->json($tableData, 200);
    }


    public function show($access_id)
    {
        # code...

        $access = Access::find($access_id);

        if (!$access) {
            return response()->json(['message' => 'Access not found'], 404);
        }

        return response()->json([
            'message' => 'Access read successfully',
            'access' => $access,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }


        $access = Access::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Access successfully created',
            'access' => $access
        ], 201);
    }

    public function update(Request $request, $access_id)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $access = Access::find($access_id);

        if (!$access) {
            return response()->json(['message' => 'Access not found'], 404);
        }



        DB::table('accesses')->where('id', $access_id)->update([
            'name' => $request->input('name', $access->name),
        ]);



        $updatedUser = DB::table('accesses')->where('id', $access_id)->first();

        return response()->json([
            'message' => 'Access successfully updated',
            'access' => $updatedUser,
        ], 200);
    }


    public function destroy($access_id)
    {
        # code...
        $access = Access::find($access_id);

        if (!$access) {
            # code...
            return response()->json([
                'message' => 'Access not found'
            ]);
        }

        $access->delete();

        return response()->json([
            'message' => 'Successfully deleted access',
        ]);
    }
}
