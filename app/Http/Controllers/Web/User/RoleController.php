<?php

namespace App\Http\Controllers\Web\User;


use App\Models\Access;
use App\Models\AccessPermission;
use App\Models\Permission;
use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    //
    public function index(Request $request)
    {
        # code...
        $search = $request->input('search', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $roles = Role::where('name', 'ilike', '%' . $search . '%')
            ->paginate($limit, ['id', 'name']);

        $tableData = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'data' => $roles->items(),
            'totalRows' => $roles->total(),
            'totalPages' => $roles->lastPage(),
            'nextPage' => $roles->nextPageUrl(),
            'prevPage' => $roles->previousPageUrl(),
        ];

        return response()->json($tableData, 200);
    }

    public function show($role_id)
    {
        # code...

        $role = Role::find($role_id);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return response()->json([
            'message' => 'Role read successfully',
            'role' => $role,
        ], 200);
    }

    public function findByIdWithAccessAndPermission($role_id)
    {
        $role = Role::select('roles.id', 'roles.name')->with(['accesses' => function ($query) {
            $query->select('accesses.id', 'accesses.name')->distinct()->with(['permissions' => function ($query) {
                $query->select('permissions.id', 'permissions.name')->withPivot('status', 'is_disable');
            }]);
        }])
            ->where('roles.id', $role_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return response()->json([
            'message' => 'Role with Access read successfully',
            'role' => $role,
        ], 200);
    }

    public function store(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            # code...
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = Role::create([
            'name' => $request->name
        ]);

        $access_ids = Access::pluck('id')->toArray();
        $permission_ids = Permission::pluck('id')->toArray();

        $unique_access_ids = array_unique($access_ids);
        $unique_permission_ids = array_unique($permission_ids);

        foreach ($unique_access_ids as $accessId) {
            foreach ($unique_permission_ids as $permissionId) {
                AccessPermission::create([
                    'role_id' => $role->id,
                    'access_id' => $accessId,
                    'permission_id' => $permissionId,
                    'status' => false,
                    'is_disable' => false,
                ]);
            }
        }

        return response()->json([
            'message' => 'Access successfully created',
            'role' => $role
        ], 201);
    }

    public function update(Request $request, $role_id)
    {
        # code...
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validator Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = Role::find($role_id);

        if (!$role) {
            # code...
            return response()->json([
                'message' => 'Role not found'
            ]);
        }

        DB::table('roles')->where('id', $role_id)->update([
            'name' => $request->input('name')
        ]);

        return response()->json([
            'message' => 'Successfully updated role',
        ]);
    }

    public function destroy($role_id)
    {
        # code...
        $role = Role::find($role_id);

        if (!$role) {
            # code...
            return response()->json([
                'message' => 'Role not found'
            ]);
        }

        $role->delete();

        return response()->json([
            'message' => 'Successfully deleted role',
        ]);
    }

    public function assignAccessAndPermission(Request $request, $role_id)
    {
        try {
            //code...
            $role = Role::find($role_id);

            if (!$role) {
                return response()->json([
                    'message' => 'Role not found'
                ]);
            }

            $access_permissions = $request->input('access_permissions'); // Assuming the request contains an array of access permissions to be updated

            foreach ($access_permissions as $access_permission_data) {
                $access_permission = AccessPermission::where([
                    'role_id' => $role->id,
                    'access_id' => $access_permission_data['access_id'],
                    'permission_id' => $access_permission_data['permission_id'],
                ])->first();

                if ($access_permission) {
                    $access_permission->update([
                        'status' => $access_permission_data['status'],
                    ]);
                }
            }

            return response()->json([
                'message' => 'Successfully updated status of Access Permissions',
            ]);
        } catch (\Exception $e) {
            //throw $th;
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e
            ]);
        }
    }
}
