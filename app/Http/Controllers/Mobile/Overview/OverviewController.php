<?php

namespace App\Http\Controllers\Mobile\Overview;

use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CpiOrder;
use Tymon\JWTAuth\Facades\JWTAuth;

class OverviewController extends Controller
{
    //
    public function index(Request $request)
    {
        $type = $request->query('type');
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $search = $request->query('search', '');
        $date = $request->query('date', '');

        $user = JWTAuth::user();
        $streamId =  $user->streams[0]['id'];

        $findSections = Section::with(['cpi_orders' => function ($query) use ($type, $month, $year) {
            $query->withCount([
                'cpi_order_has_standards as valid_standards_count' => function ($query) {
                    $query->where('status', true);
                },
                'cpi_order_has_standards as invalid_standards_count' => function ($query) {
                    $query->where('status', false);
                }
            ])
                ->when($type, function ($query, $type) {
                    return $query->where(function ($query) use ($type) {
                        $query->whereHas('documents', function ($query) use ($type) {
                            $query->where('name', $type);
                        });
                    });
                })
                ->when($month, function ($query, $month) {
                    return $query->whereMonth('cpi_orders.created_at', $month);
                })
                ->when($year, function ($query, $year) {
                    return $query->whereYear('cpi_orders.created_at', $year);
                })
                ->where('status', 'approved');
        }])
            ->has('cpi_orders.cpi_order_has_standards', '>', 0)
            ->get();

        $validCountTotal = 0;
        $invalidCountTotal = 0;
        $percentageTotal = 0;
        foreach ($findSections as $section) {
            $validStandardsCountTotal = 0;
            $invalidStandardsCountTotal = 0;
            foreach ($section->cpi_orders as $cpiOrder) {
                $validStandardsCountTotal += $cpiOrder->valid_standards_count;
                $invalidStandardsCountTotal += $cpiOrder->invalid_standards_count;
            }

            // Add the validStandardsCountTotal to the section
            $section['valid_standards_count_total'] = $validStandardsCountTotal;
            $section['invalid_standards_count_total'] = $invalidStandardsCountTotal;
            $validStandardsCountTotal == 0 && $invalidStandardsCountTotal == 0 ? $section['valid_standards_count_percentage'] = 0 : $section['valid_standards_count_percentage'] = $validStandardsCountTotal / ($validStandardsCountTotal + $invalidStandardsCountTotal) * 100;
            $validCountTotal += $section['valid_standards_count_total'];
            $invalidCountTotal += $section['invalid_standards_count_total'];
            $validCountTotal == 0 && $invalidCountTotal == 0 ? $percentageTotal = 0 : $percentageTotal = $validCountTotal / ($validCountTotal + $invalidCountTotal) * 100;
        }

        $historyCpiOrder = CpiOrder::with(['lines', 'streams', 'documents'])
            ->where('stream_id', $streamId)
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
            return $group->map(function ($order) {
                $timeAgo = $order->created_at->diffForHumans();
                $linesCount = count($order['lines']);
                return array_merge($order->toArray(), [
                    'created_ago' => $timeAgo,
                    'lines_count' => $linesCount
                ]);
            });
        })->values();

        $groupedOrdersFormatted = array_merge(...$groupedOrdersFormatted->toArray());

        $total = [
            'ok' => $validCountTotal,
            'ng' => $invalidCountTotal,
            'percentage' => $percentageTotal
        ];

        $totalAll = [
            'total' => $validCountTotal + $invalidCountTotal,
            'percentage' => $percentageTotal
        ];

        return response()->json([
            'message' => 'success',
            'data' => $findSections,
            'total' => $total,
            'totalAll' => $totalAll,
            'cpi_orders' => $groupedOrdersFormatted,
        ]);
    }
}
