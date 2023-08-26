<?php

namespace App\Http\Controllers\Mobile\Notification;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CpiOrder;
use App\Models\IsCpiOrderCorrected;
use App\Models\Notification;
use App\Models\SectionApproval;
use App\Models\VerificationApproval;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    //
    public function index()
    {
        $user = JWTAuth::user();

        $sevenDaysAgo = Carbon::now()->subDays(7);

        $findReadNotification = Notification::with(['cpi_orders' => function ($query) {
            $query->with(['documents', 'streams', 'lines']);
        }])
            ->where('user_id', $user->id)
            ->where('status', true)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->get();
        $findUnreadNotification = Notification::with(['cpi_orders' => function ($query) {
            $query->with(['documents', 'streams', 'lines']);
        }])
            ->where('user_id', $user->id)
            ->where('status', false)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->get();

        $findReadNotification = $findReadNotification->map(function ($item) {
            $timeAgo = $item->created_at->diffForHumans();
            $linesCount = count($item['cpi_orders']['lines']);
            return array_merge($item->toArray(), [
                'created_ago' => $timeAgo,
                'lines_count' => $linesCount
            ]);
        });

        $findUnreadNotification = $findUnreadNotification->map(function ($item) {
            $timeAgo = $item->created_at->diffForHumans();
            $linesCount = count($item['cpi_orders']['lines']);
            return array_merge($item->toArray(), [
                'created_ago' => $timeAgo,
                'lines_count' => $linesCount
            ]);
        });

        return response()->json([
            'readNotification' => $findReadNotification,
            'unreadNotification' => $findUnreadNotification,
        ]);
    }

    public function show($cpi_order_id)
    {
        $user = JWTAuth::user();
        $cpiOrder = CpiOrder::with([
            'cpi_order_has_sections.sections.form_control_process' => function ($query) use ($cpi_order_id) {
                $query->with([
                    'control_process_standards' => function ($query) use ($cpi_order_id) {
                        $query->with(['cpi_order_has_standards' => function ($query) use ($cpi_order_id) {
                            $query->where('cpi_order_id', $cpi_order_id);
                        }])->whereHas('cpi_order_has_standards');
                    },
                    'cpi_order_has_control_process' => function ($query) use ($cpi_order_id) {
                        $query
                            ->with(['cpi_order_has_control_process_photos'])
                            ->where('cpi_order_id', $cpi_order_id);
                    }
                ]);
            },
            'cpi_order_has_sections.lines',
            'samplings',
            'problems',
            'section_approvals.decline_reasons',
            'verification_approvals.decline_reasons',
            'documents',
            'users',
            'streams'
        ])
            ->where('user_id', $user->id)
            ->find($cpi_order_id);
        $sectionApproval = SectionApproval::with(['stream_section_heads.section'])
            ->where('cpi_order_id', $cpi_order_id)->get();
        $verificationApproval = VerificationApproval::with(['stream_verifications'])
            ->where('cpi_order_id', $cpi_order_id)->get();

        $isCpiOrderCorrected = IsCpiOrderCorrected::where('cpi_order_before_id', $cpi_order_id)->first();

        if (!$cpiOrder) {
            return response()->json([
                'message' => 'CpiOrder not found'
            ], 404);
        }

        $isCpiOrderCorrected = IsCpiOrderCorrected::where('cpi_order_before_id', $cpi_order_id)->first();
        if (!$isCpiOrderCorrected) {
            $isCpiOrderCorrected = false;
        } else {
            $isCpiOrderCorrected = true;
        }

        Notification::where('cpi_order_id', $cpi_order_id)
            ->update([
                'status' => true,
                'updated_at' => now()
            ]);

        return response()->json([
            "message" => "CpiOrder found",
            "data" => $cpiOrder,
            'sectionApproval' => $sectionApproval,
            'verificationApproval' => $verificationApproval,
            'isCpiOrderCorrected' => $isCpiOrderCorrected
        ]);
    }
}
