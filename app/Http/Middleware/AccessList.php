<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessList
{
    public const DASHBOARD = "Dashboard";
    public const APPROVAL = "Approval";
    public const REPORT = "Report";
    public const CLAIM = "Claim";
    public const PROCEDURE = "Procedure";
    public const IMPORT = "Import";
    public const MASTER_DATA = "Master Data";
    public const STREAM = "Stream";
    public const SECTION = "Section";
    public const FORM = "Form";
    public const MANAGEMENT = "Management";
    public const WORK_FLOW = "Work Flow";
}
