<?php

namespace App\Http\Controllers\Web\User;


use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PermissionController extends Controller
{
    //
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $permission = Permission::where('name', 'ilike', '%' . $search . '%')
            ->paginate($limit, ['id', 'name',]);

        $tableData = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'data' => $permission->items(),
            'totalRows' => $permission->total(),
            'totalPages' => $permission->lastPage(),
            'nextPage' => $permission->nextPageUrl(),
            'prevPage' => $permission->previousPageUrl(),
        ];

        return response()->json($tableData, 200);
    }


    public function show($permission_id)
    {
        # code...

        $permission = Permission::find($permission_id);

        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        return response()->json([
            'message' => 'Permission read successfully',
            'permission' => $permission,
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


        $permission = Permission::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Permission successfully created',
            'permission' => $permission
        ], 201);
    }

    public function update(Request $request, $permission_id)
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

        $permission = Permission::find($permission_id);

        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }



        DB::table('permissions')->where('id', $permission_id)->update([
            'name' => $request->input('name', $permission->name),
        ]);



        $updatedUser = DB::table('permissions')->where('id', $permission_id)->first();

        return response()->json([
            'message' => 'Permission successfully updated',
            'permission' => $updatedUser,
        ], 200);
    }


    public function destroy($permission_id)
    {
        # code...
        $permission = Permission::find($permission_id);

        if (!$permission) {
            # code...
            return response()->json([
                'message' => 'Permission not found'
            ]);
        }

        $permission->delete();

        return response()->json([
            'message' => 'Successfully deleted permission',
        ]);
    }
}
