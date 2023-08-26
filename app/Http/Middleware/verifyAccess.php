<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class verifyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * 
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = Str::replaceFirst('Bearer ', '', $request->header('Authorization'));
            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json(['error' => "You don't have access to use this transaction"], 401);
            }

            $accessFound = false;
            $permissionFound = false;

            if ($user->role) {
                $roleAccesses = $user->role->accesses;

                $allowedPermissions = [
                    PermissionList::CREATE,
                    PermissionList::UPDATE,
                    PermissionList::READ,
                    PermissionList::DELETE,
                    PermissionList::IMPORT,
                    PermissionList::UPDATE
                ];

                $allowedAccesses = [
                    AccessList::DASHBOARD,
                    AccessList::APPROVAL,
                    AccessList::REPORT,
                    AccessList::CLAIM,
                    AccessList::PROCEDURE,
                    AccessList::IMPORT,
                    AccessList::MASTER_DATA,
                    AccessList::STREAM,
                    AccessList::SECTION,
                    AccessList::FORM,
                    AccessList::MANAGEMENT,
                    AccessList::WORK_FLOW
                ];

                foreach ($roleAccesses as $roleAccess) {
                    if (in_array($roleAccess->name, $allowedAccesses)) {
                        $accessFound = true;
                    }

                    foreach ($roleAccess->permissions as $roleAccessPermission) {
                        if (in_array($roleAccessPermission->name, $allowedPermissions)) {
                            $permissionFound = true;
                        }
                    }

                    if ($accessFound && $permissionFound) {
                        break;
                    }
                }
            }

            if (!$accessFound || !$permissionFound) {
                return response()->json(['error' => "You don't have access to use this transaction"], 401);
            }


            return $next($request);
        } catch (\Throwable $error) {
            return response()->json(['error' => $error->getMessage()]);
        }
    }
}
