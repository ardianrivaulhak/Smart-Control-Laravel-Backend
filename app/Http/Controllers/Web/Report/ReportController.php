<?php


namespace App\Http\Controllers\Web\Report;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CpiOrder;
use App\Models\LogTrailDeclined;
use App\Models\LogTrailDetail;
use App\Models\SectionApproval;
use App\Models\VerificationApproval;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{

    public function indexClaim(Request $request)
    {
        # code..
        $status = $request->query('status');
        $search = $request->input('search', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $date = $request->query("date");

        $findAllCpiOrder = CpiOrder::with('documents')
            ->join('streams', 'cpi_orders.stream_id', '=', 'streams.id')
            ->join('cpi_order_has_sections', 'cpi_orders.id', '=', 'cpi_order_has_sections.cpi_order_id')
            ->join('sections', 'cpi_order_has_sections.section_id', '=', 'sections.id')
            ->join('lines', 'cpi_order_has_sections.line_id', '=', 'lines.id')
            ->where(function ($query) use ($search) {
                $query->whereHas('streams', function ($query) use ($search) {
                    $query->where('name', 'ilike', '%' . $search . '%');
                })
                    ->orWhereHas('sections', function ($query) use ($search) {
                        $query->where('name', 'ilike', '%' . $search . '%');
                    });
            })
            ->where(function ($query) use ($status) {
                if (!empty($status)) {
                    $query->where('status', $status);
                }
            })
            ->where(function ($query) use ($date) {
                if (!empty($date)) {
                    $query->whereDate('cpi_orders.created_at', $date);
                }
            })
            ->whereHas('documents', function ($query) {
                $query->where('name', 'like', '%Claim%');
            })
            ->select(
                'cpi_orders.id',
                'cpi_orders.stream_id',
                'cpi_orders.document_id',
                'cpi_orders.user_id',
                'cpi_orders.status',
                'cpi_orders.rev',
                'cpi_orders.created_at as date',
                'streams.name as stream_name',
                'sections.name as sections_name',
                'lines.name as line_name'
            )
            ->paginate($limit);



        $tableData = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'data' => $findAllCpiOrder->items(),
            'totalRows' => $findAllCpiOrder->total(),
            'totalPages' => $findAllCpiOrder->lastPage(),
            'nextPage' => $findAllCpiOrder->nextPageUrl(),
            'prevPage' => $findAllCpiOrder->previousPageUrl(),
        ];


        return response()->json([
            'message' => "Successfully read data",
            'data' => $tableData
        ], 200);
    }

    public function indexProcedure(Request $request)
    {
        # code..
        $status = $request->query('status');
        $search = $request->input('search', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $date = $request->query("date");

        $findAllCpiOrder = CpiOrder::with('documents')
            ->join('streams', 'cpi_orders.stream_id', '=', 'streams.id')
            ->join('cpi_order_has_sections', 'cpi_orders.id', '=', 'cpi_order_has_sections.cpi_order_id')
            ->join('sections', 'cpi_order_has_sections.section_id', '=', 'sections.id')
            ->join('lines', 'cpi_order_has_sections.line_id', '=', 'lines.id')
            ->where(function ($query) use ($search) {
                $query->whereHas('streams', function ($query) use ($search) {
                    $query->where('name', 'ilike', '%' . $search . '%');
                })
                    ->orWhereHas('sections', function ($query) use ($search) {
                        $query->where('name', 'ilike', '%' . $search . '%');
                    });
            })
            ->whereHas('sections', function ($query) use ($search) {
                $query->where('name', 'ilike', '%' . $search . '%');
            })
            ->where(function ($query) use ($status) {
                if (!empty($status)) {
                    $query->where('status', $status);
                }
            })
            ->where(function ($query) use ($date) {
                if (!empty($date)) {
                    $query->whereDate('cpi_orders.created_at', $date);
                }
            })
            ->whereHas('documents', function ($query) {
                $query->where('name', 'like', '%Procedure%');
            })
            ->select(
                'cpi_orders.id',
                'cpi_orders.stream_id',
                'cpi_orders.document_id',
                'cpi_orders.user_id',
                'cpi_orders.status',
                'cpi_orders.rev',
                'cpi_orders.created_at as date',
                'streams.name as stream_name',
                'sections.name as sections_name',
                'lines.name as line_name'
            )
            ->paginate($limit);



        $tableData = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'data' => $findAllCpiOrder->items(),
            'totalRows' => $findAllCpiOrder->total(),
            'totalPages' => $findAllCpiOrder->lastPage(),
            'nextPage' => $findAllCpiOrder->nextPageUrl(),
            'prevPage' => $findAllCpiOrder->previousPageUrl(),
        ];


        return response()->json([
            'message' => "Successfully read data",
            'data' => $tableData
        ], 200);
    }

    public function indexPokayoke(Request $request)
    {
        # code..
        $status = $request->query('status');
        $search = $request->input('search', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $date = $request->query("date");
        $findAllCpiOrder = CpiOrder::with('documents')
            ->join('streams', 'cpi_orders.stream_id', '=', 'streams.id')
            ->join('cpi_order_has_sections', 'cpi_orders.id', '=', 'cpi_order_has_sections.cpi_order_id')
            ->join('sections', 'cpi_order_has_sections.section_id', '=', 'sections.id')
            ->join('lines', 'cpi_order_has_sections.line_id', '=', 'lines.id')
            ->where(function ($query) use ($search) {
                $query->whereHas('streams', function ($query) use ($search) {
                    $query->where('name', 'ilike', '%' . $search . '%');
                })
                    ->orWhereHas('sections', function ($query) use ($search) {
                        $query->where('name', 'ilike', '%' . $search . '%');
                    });
            })
            ->where(function ($query) use ($status) {
                if (!empty($status)) {
                    $query->where('status', $status);
                }
            })
            ->where(function ($query) use ($date) {
                if (!empty($date)) {
                    $query->whereDate('cpi_orders.created_at', $date);
                }
            })
            ->whereHas('documents', function ($query) {
                $query->where('name', 'like', '%Pokayoke & TJDF%');
            })
            ->select(
                'cpi_orders.id',
                'cpi_orders.stream_id',
                'cpi_orders.document_id',
                'cpi_orders.user_id',
                'cpi_orders.status',
                'cpi_orders.rev',
                'cpi_orders.created_at as date',
                'streams.name as stream_name',
                'sections.name as sections_name',
                'lines.name as line_name'
            )
            ->paginate($limit);



        $tableData = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'data' => $findAllCpiOrder->items(),
            'totalRows' => $findAllCpiOrder->total(),
            'totalPages' => $findAllCpiOrder->lastPage(),
            'nextPage' => $findAllCpiOrder->nextPageUrl(),
            'prevPage' => $findAllCpiOrder->previousPageUrl(),
        ];


        return response()->json([
            'message' => "Successfully read data",
            'data' => $tableData
        ], 200);
    }

    public function show($cpi_order_id)
    {
        $result_cpiOrderDoc = new \stdClass();
        $result_sectionApproval = [];
        $result_verificationApproval = [];
        $cpiOrderDoc = CpiOrder::with(['users', 'streams', 'documents'])
            ->select('id', 'stream_id', 'document_id', 'user_id', 'status', 'rev')
            ->find($cpi_order_id);

        $result_cpiOrderDoc->id = $cpiOrderDoc->id;
        $result_cpiOrderDoc->rev = $cpiOrderDoc->rev;
        $result_cpiOrderDoc->status = $cpiOrderDoc->status;
        $result_cpiOrderDoc->user_name = $cpiOrderDoc->users->name;
        $result_cpiOrderDoc->stream_name = $cpiOrderDoc->streams->name;
        $result_cpiOrderDoc->document_name = $cpiOrderDoc->documents->name;


        $sectionApproval = SectionApproval::with(['stream_section_heads.section'])
            ->where('cpi_order_id', $cpi_order_id)->get();

        foreach ($sectionApproval as $item) {
            $obj = new \stdClass();
            $obj->id = $item->id;
            $obj->status = $item->status;
            $obj->section_name = $item->stream_section_heads->section->name;
            $result_sectionApproval[] = $obj;
        }


        $verificationApproval = VerificationApproval::with(['stream_verifications'])
            ->where('cpi_order_id', $cpi_order_id)->get();

        foreach ($verificationApproval as $item_verificationApproval) {
            # code...
            $obj_verificationApproval = new \stdClass();
            $obj_verificationApproval->id = $item_verificationApproval->id;
            $obj_verificationApproval->status = $item_verificationApproval->status;
            $obj_verificationApproval->type =  $item_verificationApproval->stream_verifications->type;
            $obj_verificationApproval->name = $item_verificationApproval->stream_verifications->name;
            $result_verificationApproval[] = $obj_verificationApproval;
        }


        $detailCpiOrder = CpiOrder::with([
            'cpi_order_has_sections.sections.form_control_process' => function ($query) use ($cpi_order_id) {
                $query->with(['control_process_standards.cpi_order_has_standards' => function ($query) use ($cpi_order_id) {
                    $query->where('cpi_order_id', $cpi_order_id);
                }, 'cpi_order_has_control_process_photos']);
            },
            'cpi_order_has_sections.lines',
            'samplings',
            'problems',
        ])
            ->select('id', 'stream_id', 'document_id', 'user_id', 'status', 'rev')
            ->find($cpi_order_id);


        if (!$detailCpiOrder) {
            return response()->json([
                'message' => 'CpiOrder not found'
            ], 404);
        }


        return response()->json([
            'data' => [
                'cpiOrderDoc' => $result_cpiOrderDoc,
                'sectionApproval' => $result_sectionApproval,
                'VerificationApproval' => $result_verificationApproval,
                'detailCpiOrder' => $detailCpiOrder
            ]
        ]);
    }

    public function downloadCsvClaim(Request $request)
    {

        $date = $request->query("date");

        $findAllCpiOrder = CpiOrder::with('documents')
            ->join('streams', 'cpi_orders.stream_id', '=', 'streams.id')
            ->join('cpi_order_has_sections', 'cpi_orders.id', '=', 'cpi_order_has_sections.cpi_order_id')
            ->join('sections', 'cpi_order_has_sections.section_id', '=', 'sections.id')
            ->join('lines', 'cpi_order_has_sections.line_id', '=', 'lines.id')
            ->where(function ($query) use ($date) {
                if (!empty($date)) {
                    $query->whereDate('cpi_orders.created_at', $date);
                }
            })
            ->whereHas('documents', function ($query) {
                $query->where('name', 'like', '%Claim%');
            })
            ->select(
                'cpi_orders.id',
                'cpi_orders.status',
                'cpi_orders.rev',
                'streams.name as stream_name',
                'sections.name as sections_name',
                'lines.name as line_name',
                'cpi_orders.created_at'
            );

        $data = $findAllCpiOrder->get();
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=claim_data.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'Rev', 'Date', 'Stream', 'Section', 'Status']);

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->rev,
                    $row->created_at,
                    $row->stream_name,
                    $row->sections_name,
                    $row->status,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
        // chmod($response, 0444);
        return $response;
    }

    public function downloadCsvProcedure(Request $request)
    {

        $date = $request->query("date");

        $findAllCpiOrder = CpiOrder::with('documents')
            ->join('streams', 'cpi_orders.stream_id', '=', 'streams.id')
            ->join('cpi_order_has_sections', 'cpi_orders.id', '=', 'cpi_order_has_sections.cpi_order_id')
            ->join('sections', 'cpi_order_has_sections.section_id', '=', 'sections.id')
            ->join('lines', 'cpi_order_has_sections.line_id', '=', 'lines.id')
            ->where(function ($query) use ($date) {
                if (!empty($date)) {
                    $query->whereDate('cpi_orders.created_at', $date);
                }
            })
            ->whereHas('documents', function ($query) {
                $query->where('name', 'like', '%Procedure%');
            })
            ->select(
                'cpi_orders.id',
                'cpi_orders.status',
                'cpi_orders.rev',
                'streams.name as stream_name',
                'sections.name as sections_name',
                'lines.name as line_name',
                'cpi_orders.created_at'
            );

        $data = $findAllCpiOrder->get();
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=claim_data.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'Rev', 'Date', 'Stream', 'Section', 'Status']);

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->rev,
                    $row->created_at,
                    $row->stream_name,
                    $row->sections_name,
                    $row->status,
                ]);
            }

            fclose($handle);
        }, 200, $headers);

        return $response;
    }

    public function downloadCsvPokayoke(Request $request)
    {

        $date = $request->query("date");

        $findAllCpiOrder = CpiOrder::with('documents')
            ->join('streams', 'cpi_orders.stream_id', '=', 'streams.id')
            ->join('cpi_order_has_sections', 'cpi_orders.id', '=', 'cpi_order_has_sections.cpi_order_id')
            ->join('sections', 'cpi_order_has_sections.section_id', '=', 'sections.id')
            ->join('lines', 'cpi_order_has_sections.line_id', '=', 'lines.id')
            ->where(function ($query) use ($date) {
                if (!empty($date)) {
                    $query->whereDate('cpi_orders.created_at', $date);
                }
            })
            ->whereHas('documents', function ($query) {
                $query->where('name', 'like', '%Pokayoke & TJDF%');
            })
            ->select(
                'cpi_orders.id',
                'cpi_orders.status',
                'cpi_orders.rev',
                'streams.name as stream_name',
                'sections.name as sections_name',
                'lines.name as line_name',
                'cpi_orders.created_at'
            );

        $data = $findAllCpiOrder->get();
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=claim_data.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'Rev', 'Date', 'Stream', 'Section', 'Status']);

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->rev,
                    $row->created_at,
                    $row->stream_name,
                    $row->sections_name,
                    $row->status,
                ]);
            }

            fclose($handle);
        }, 200, $headers);

        return $response;
    }

    public function indexLogTrailsDeclineds(Request $request)
    {
        $search = $request->input('search', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $arr = [];
        $data = LogTrailDeclined::where('change', 'ilike', '%' . $search . '%')
            ->with(['cpi_orders', 'decline_reason'])
            ->paginate($limit);
        foreach ($data as $_data) {
            # code...
            $obj_logTrails = new \stdClass();
            $obj_logTrails->id = $_data->id;
            $obj_logTrails->timestamp = $_data->timestamp;
            $obj_logTrails->rev = $_data->cpi_orders->rev;
            $obj_logTrails->change = $_data->change;
            $obj_logTrails->inspector = $_data->inspector;
            $obj_logTrails->declined_by = $_data->declined_by;
            $obj_logTrails->reason = $_data->decline_reason->reason;
            $arr[] = $obj_logTrails;
        }

        $tableData = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'data' => $arr,
            'totalRows' => $data->total(),
            'totalPages' => $data->lastPage(),
            'nextPage' => $data->nextPageUrl(),
            'prevPage' => $data->previousPageUrl(),
        ];

        return response()->json($tableData, 200);
    }
}
