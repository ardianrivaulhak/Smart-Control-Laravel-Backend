<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionList
{

    public const CREATE = "create";
    public const READ = "read";
    public const UPDATE = "update";
    public const DELETE = "delete";
    public const EXPORT = "export";
    public const IMPORT = "import";
}
