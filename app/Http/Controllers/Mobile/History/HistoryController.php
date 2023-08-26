<?php

namespace App\Http\Controllers\Mobile\History;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CpiOrder;
use App\Models\CpiOrderExit;
use App\Models\CpiOrderHasControlProcess;
use App\Models\CpiOrderHasControlProcessPhoto;
use App\Models\CpiOrderHasProblem;
use App\Models\CpiOrderHasSampling;
use App\Models\CpiOrderHasSection;
use App\Models\CpiOrderHasStandard;
use App\Models\IsCpiOrderCorrected;
use App\Models\Problem;
use App\Models\Sampling;
use App\Models\SectionApproval;
use App\Models\VerificationApproval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Facades\JWTAuth;

class HistoryController extends Controller
{
    //

    public function index()
    {
        $user = JWTAuth::user();
        $onGoingDocument =  CpiOrder::with(['cpi_order_exits', 'sections', 'lines', 'streams', 'documents'])
            ->where('user_id', $user->id)
            ->whereHas('cpi_order_exits')
            ->get();

        return response()->json(['message' => 'success', 'data' => $onGoingDocument], 200);
    }

    public function historyList(Request $request)
    {
        $status = $request->query('status', '');
        $search = $request->query('search', '');
        $date = $request->query('date', '');

        $user = JWTAuth::user();
        $historyCpiOrder = CpiOrder::with(['lines', 'streams', 'documents'])
            ->where('user_id', $user->id)
            ->whereRaw('LOWER(status) LIKE ?', ['%' . strtolower($status) . '%'])
            ->whereDoesntHave('cpi_order_exits')
            ->whereHas('lines')
            ->whereOr(function ($query) use ($search) {
                $query->whereHas('documents', function ($query) use ($search) {
                    $query->where('name', 'ilike', '%' . $search . '%');
                });
            })
            ->when($date, function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })
            ->get();

        $groupedOrders = $historyCpiOrder->groupBy(function ($item) {
            return $item->created_at->format('d-m-Y');
        });

        $groupedOrdersFormatted = $groupedOrders->map(function ($group) {
            return [
                'date' => $group->first()->created_at->format('d/m/Y'),
                'orders' => $group->map(function ($order) {
                    $timeAgo = $order->created_at->diffForHumans();
                    $linesCount = count($order['lines']); // Count the number of lines per order
                    return array_merge($order->toArray(), [
                        'created_ago' => $timeAgo,
                        'lines_count' => $linesCount
                    ]);
                })
            ];
        })->values();

        return response()->json(['message' => 'success', 'data' => $groupedOrdersFormatted], 200);
    }

    public function deleteOnGoingDocument($cpi_order_exit_id)
    {
        $findCpiOrderExit = CpiOrderExit::find($cpi_order_exit_id);

        if (!$findCpiOrderExit) {
            return response()->json(['message' => 'CPI Order not found'], 404);
        }
        try {
            DB::beginTransaction();
            $cpiOrderControlProcesses = CpiOrderHasControlProcess::with(['cpi_order_has_control_process_photos'])->get();
            foreach ($cpiOrderControlProcesses as $cpiOrderControlProcess) {
                foreach ($cpiOrderControlProcess['cpi_order_has_control_process_photos'] as $photo) {
                    $sourcePath = storage_path('app/' . str_replace('storage', 'public', $photo['photo_url']));
                    if (File::exists($sourcePath)) {
                        File::delete($sourcePath);
                    }
                }
            }
            CpiOrderExit::where('cpi_order_id', $findCpiOrderExit['cpi_order_id'])->forceDelete();
            CpiOrderHasSection::where('cpi_order_id', $findCpiOrderExit['cpi_order_id'])->forceDelete();
            CpiOrderHasStandard::where('cpi_order_id', $findCpiOrderExit['cpi_order_id'])->forceDelete();
            CpiOrderHasControlProcessPhoto::with(['cpi_order_has_control_process' => function ($query) use ($findCpiOrderExit) {
                $query->where('cpi_order_id', $findCpiOrderExit['cpi_order_id']);
            }])->forceDelete();
            CpiOrderHasControlProcess::where('cpi_order_id', $findCpiOrderExit['cpi_order_id'])->forceDelete();
            Problem::with(['cpi_orders' => function ($query) use ($findCpiOrderExit) {
                $query->where('id', $findCpiOrderExit['cpi_order_id']);
            }])->forceDelete();
            Sampling::with(['cpi_orders' => function ($query) use ($findCpiOrderExit) {
                $query->where('id', $findCpiOrderExit['cpi_order_id']);
            }])->forceDelete();
            CpiOrderHasProblem::where('cpi_order_id', $findCpiOrderExit['cpi_order_id'])->forceDelete();
            CpiOrderHasSampling::where('cpi_order_id', $findCpiOrderExit['cpi_order_id'])->forceDelete();

            DB::commit();
            return response()->json(['message' => 'On Going Document Deleted'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($photoUrls)) {
                foreach ($photoUrls as $photoUrl) {
                    $fileName = pathinfo($photoUrl)['basename'];
                    $destinationPath = storage_path('app/public/temp/' . $fileName);
                    File::move($photoUrl, $destinationPath);
                }
            }
            return response()->json([
                "message" => "Internal Server Error",
                "error" => $e->getMessage()
            ]);
        }
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

        return response()->json([
            "message" => "CpiOrder found",
            "data" => $cpiOrder,
            'sectionApproval' => $sectionApproval,
            'verificationApproval' => $verificationApproval,
            'isCpiOrderCorrected' => $isCpiOrderCorrected
        ]);
    }
}
