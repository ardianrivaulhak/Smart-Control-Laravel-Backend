<?php

namespace App\Http\Controllers\Web\Dashboard;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CpiOrder;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $month = $request->input('month');
        $year = $request->query('year');
        $c = CpiOrder::join("streams", 'cpi_orders.stream_id', '=', 'streams.id')
            ->join("cpi_order_has_standards", 'cpi_orders.id', '=', 'cpi_order_has_standards.cpi_order_id')
            ->join("control_process_standards", 'cpi_order_has_standards.control_process_standard_id', '=', 'control_process_standards.id')
            ->where(function ($query) use ($search) {
                $query->whereHas("streams", function ($query) use ($search) {
                    $query->where("name", 'ilike', '%' . $search . '%');
                });
            })
            ->where(function ($query) use ($month) {
                if (!empty($month)) {
                    $query->whereMonth('cpi_orders.created_at', $month);
                }
            })
            ->where(function ($query) use ($year) {
                if (!empty($year)) {
                    $query->whereYear('cpi_orders.created_at', $year); // Menambah filter berdasarkan tahun
                }
            })
            ->where('cpi_orders.status', '=', 'approved')
            ->select(
                "cpi_orders.id",
                'cpi_orders.stream_id',
                'cpi_orders.status as status_cpi_order',
                'cpi_orders.rev',
                'streams.name as stream_name',
                'cpi_order_has_standards.id as cpi_order_has_standards_id',
                'cpi_order_has_standards.status as status_cpi_order_has_standards',
                'cpi_order_has_standards.description as description_cpi_order_has_standards'
            )
            ->paginate($limit);

        $streamNames = $c->groupBy('streams.name')->pluck('0.stream_name');
        $streamNamesArray = $streamNames->toArray();

        $totalOk = $c->groupBy('streams.name')
            ->map(function ($groupedItems) {
                return $groupedItems->filter(function ($value) {
                    return $value->status_cpi_order_has_standards === true;
                })->count();
            })
            ->flatten();

        $totalNg = $c->groupBy("streams.name")
            ->map(function ($groupedItems) {
                return $groupedItems->filter(function ($value) {
                    return $value->status_cpi_order_has_standards === false;
                })->count();
            })
            ->flatten();

        $result = [];

        foreach ($totalOk as $key => $value) {
            $totalNgValue = $totalNg[$key] ?? 0;
            $percentage = ($value / ($value + $totalNgValue)) * 100;
            $color = "";
            if ($percentage === 100) {
                # code...
                $color = "Green";
            } else if ($percentage >= 91 && $percentage <= 99) {
                # code...
                $color = "Yellow";
            } else {
                $color = "Red";
            }
            $result[] = [
                'steam_name' => $streamNamesArray[$key],
                'percentage' => $percentage,
                'color' => $color,
            ];
        }

        $tableData = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'data' => $result,
            'totalRows' => $c->total(),
            'totalPages' => $c->lastPage(),
            'nextPage' => $c->nextPageUrl(),
            'prevPage' => $c->previousPageUrl(),
        ];

        return response()->json([
            'message' => 'Successfully read data',
            'data' => $tableData
        ]);
    }

    public function cpiInformation(Request $request)
    {

        $limit = $request->input('limit', 10);

        $cpiClaim = CpiOrder::with("sections", "cpi_order_has_standards")
            ->join("streams", 'cpi_orders.stream_id', '=', 'streams.id')
            ->join("documents", 'cpi_orders.document_id', '=', 'documents.id')
            ->where('cpi_orders.status', '=', 'approved')
            ->select(
                "cpi_orders.id",
                'documents.name as document_name',
                'cpi_orders.status as status_cpi_order',
                'cpi_orders.rev',
                'streams.name as stream_name',
            )
            ->paginate($limit);

        $_data = [];
        foreach ($cpiClaim as $claim) {
            foreach ($claim->sections as $section) {
                foreach ($claim->cpi_order_has_standards as $status) {
                    $objClaim = new \stdClass();
                    $objClaim->id = $claim->id;
                    $objClaim->stream_name = $claim->stream_name;
                    $objClaim->document_name = $claim->document_name;
                    $objClaim->section = $section->name;
                    $objClaim->status = $status->status ? "Ok" : "Ng";
                    $_data[] = $objClaim;
                }
            }
        }


        $statusCounts = [];

        foreach ($_data as $data) {
            $key = $data->stream_name . '_' . $data->section;
            if (!isset($statusCounts[$key])) {
                $statusCounts[$key] = ['Ok' => 0, 'Ng' => 0];
            }
            $data->status === 'Ok' ? $statusCounts[$key]['Ok']++ : $statusCounts[$key]['Ng']++;
        }

        $percentageResults = [];

        foreach ($statusCounts as $key => $counts) {
            [$streamName, $sectionName] = explode('_', $key);
            $okCount = $counts['Ok'];
            $ngCount = $counts['Ng'];
            $total = $okCount + $ngCount;
            $percentage = ($total > 0) ? ($okCount / $total) * 100 : 0;
            $documentName = $cpiClaim[0]->document_name;
            $result = new \stdClass();
            $result->stream_name = $streamName;
            $result->section_name = $sectionName;
            $result->document_name = $documentName;
            $result->percentage = $percentage;
            $percentageResults[] = $result;
        }

        return response()->json([
            'message' => 'Successfully read data',
            'data' => $percentageResults
        ]);
    }

    public function sumary(Request $request)
    {
        $limit = $request->input('limit', 10);
        $interval = $request->input('interval', 'monthly');

        $c = CpiOrder::with("cpi_order_has_standards")
            ->join("streams", 'cpi_orders.stream_id', '=', 'streams.id')
            ->join("documents", 'cpi_orders.document_id', '=', 'documents.id')
            ->join("cpi_order_has_standards", 'cpi_orders.id', '=', 'cpi_order_has_standards.cpi_order_id')
            ->join("control_process_standards", 'cpi_order_has_standards.control_process_standard_id', '=', 'control_process_standards.id')
            ->where(function ($query) use ($interval) {
                if ($interval === 'weekly') {
                    $weekStart = now()->startOfWeek();
                    $weekEnd = now()->endOfWeek();
                    $query->whereBetween('cpi_orders.created_at', [$weekStart, $weekEnd]);
                } elseif ($interval === 'monthly') {
                    $query->whereMonth('cpi_orders.created_at', now()->month);
                } elseif ($interval === 'daily') {
                    $query->whereDate('cpi_orders.created_at', now()->toDateString());
                }
            })
            ->where('cpi_orders.status', '=', 'approved')
            ->select(
                "cpi_orders.id",
                'cpi_orders.stream_id',
                'cpi_orders.status as status_cpi_order',
                'cpi_orders.rev',
                'documents.name as document_name',
                'streams.name as stream_name',
            )
            ->paginate($limit);


        $documentStatusCounts = $c->groupBy('document_name')
            ->map(function ($groupedItems) {
                $statusCounts = [
                    'Ok' => 0,
                    'Ng' => 0,
                ];

                foreach ($groupedItems as $item) {
                    foreach ($item->cpi_order_has_standards as $standard) {
                        if ($standard['status'] === true) {
                            $statusCounts['Ok']++;
                        } else {
                            $statusCounts['Ng']++;
                        }
                    }
                }

                return $statusCounts;
            });



        return response()->json([
            'message' => 'Successfully read data',
            'data' => $documentStatusCounts
        ]);
    }

    public function historyNg(Request $request)
    {
        $search = $request->input('search', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $date = $request->query("date");
        $document_id = $request->query('document_id');

        $cpi = CpiOrder::with("cpi_order_has_standards")
            ->join("streams", 'cpi_orders.stream_id', '=', 'streams.id')
            ->join("documents", 'cpi_orders.document_id', '=', 'documents.id')
            ->where('cpi_orders.status', '=', 'approved')
            ->where(function ($query) use ($search) {
                $query->whereHas("streams", function ($query) use ($search) {
                    $query->where("name", 'ilike', '%' . $search . '%');
                });
            })
            ->where(function ($query) use ($date) {
                if (!empty($date)) {
                    $query->whereDate('cpi_orders.created_at', $date);
                }
            })
            ->where(function ($query) use ($document_id) {
                if (!empty($document_id)) {
                    $query->where('documents.id', $document_id);
                }
            })
            ->select(
                "cpi_orders.id",
                'documents.id as document_id',
                'documents.name as document_name',
                'streams.name as stream_name',
                'cpi_orders.created_at'
            )
            ->withCount(['cpi_order_has_standards as Ng' => function ($query) {
                $query->where('status', false);
            }])
            ->paginate($limit);

        $arr = [];
        foreach ($cpi as $cpiOrder) {
            $obj = new \stdClass();
            $obj->timestamp = Carbon::parse($cpiOrder->created_at)->format('Y-m-d');
            $obj->document_id = $cpiOrder->document_id;
            $obj->document_name = $cpiOrder->document_name;
            $obj->stream_name = $cpiOrder->stream_name;
            $obj->status = $cpiOrder->Ng;
            $arr[] = $obj;
        }

        $tableData = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'data' => $arr,
            'totalRows' => $cpi->total(),
            'totalPages' => $cpi->lastPage(),
            'nextPage' => $cpi->nextPageUrl(),
            'prevPage' => $cpi->previousPageUrl(),
        ];

        return response()->json([
            'message' => 'Successfully read data',
            'data' => $tableData
        ]);
    }
}
