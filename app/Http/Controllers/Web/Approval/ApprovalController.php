<?php

namespace App\Http\Controllers\Web\Approval;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Models\CpiOrder;
use App\Models\DeclineReason;
use App\Models\LogTrailDeclined;
use App\Models\Notification;
use App\Models\SectionApproval;
use App\Models\Stream;
use App\Models\StreamSectionHead;
use App\Models\StreamVerification;
use App\Models\VerificationApproval;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApprovalController extends Controller
{
    //
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);
        $sort = $request->query('sort', 'created_at');
        $order = $request->query('order', 'desc');
        $type = $request->query('type', '');


        $user = JWTAuth::user();
        $status = $request->query('status', 'waiting');
        $date = $request->query("date");
        $findAllCpiOrder = null;
        if ($user->stream_section_head) {
            if ($status == 'waiting') {
                $findAllCpiOrder = CpiOrder::with([
                    'streams.stream_section_head' => function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    },
                    'sections',
                    'lines',
                    'documents' => function ($query) use ($sort, $order) {
                        $query->when($sort === 'name', function ($query) use ($sort, $order) {
                            $query->orderBy($sort, $order);
                        });
                    },
                    'streams' => function ($query) use ($user) {
                        $query->where('name', $user->streams[0]->name);
                    }
                ])->where('status', 'waiting')
                    ->where(function ($query) use ($search) {
                        $query->whereHas('sections', function ($query) use ($search) {
                            $query->where('name', 'ilike', '%' . $search . '%');
                        });
                    })
                    ->where(function ($query) use ($type) {
                        $query->whereHas('documents', function ($query) use ($type) {
                            $query->where('name', 'ilike', '%' . $type . '%');
                        });
                    })
                    ->where(function ($query) use ($date) {
                        if (!empty($date)) {
                            $query->whereDate('cpi_orders.created_at', $date);
                        }
                    })
                    ->whereHas('streams.stream_section_head', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->skip(($page - 1) * $limit)
                    ->take($limit)
                    ->when($sort === 'created_at', function ($query) use ($sort, $order) {
                        $query->orderBy($sort, $order);
                    })
                    ->when($sort === 'rev', function ($query) use ($sort, $order) {
                        $query->orderBy($sort, $order);
                    })
                    ->paginate();

                foreach ($findAllCpiOrder as &$cpiOrder) {
                    if (isset($cpiOrder['section_approvals'])) {
                        $cpiOrder['approvals'] = $cpiOrder['section_approvals'];
                        unset($cpiOrder['section_approvals']);
                    }
                }
            } else if ($status == 'confirmed') {
                $findAllCpiOrder = CpiOrder::with([
                    'streams.stream_section_head' => function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    },
                    'sections',
                    'lines',
                    'documents' => function ($query) use ($sort, $order) {
                        $query->when($sort === 'name', function ($query) use ($sort, $order) {
                            $query->orderBy($sort, $order);
                        });
                    },
                ])
                    ->where('status', 'approved')
                    ->where(function ($query) use ($date) {
                        if (!empty($date)) {
                            $query->whereDate('cpi_orders.created_at', $date);
                        }
                    })
                    ->orWhere('status', 'declined')
                    ->whereHas('streams.stream_section_head', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->where(function ($query) use ($search) {
                        $query->whereHas('sections', function ($query) use ($search) {
                            $query->where('name', 'ilike', '%' . $search . '%');
                        });
                    })
                    ->where(function ($query) use ($type) {
                        $query->whereHas('documents', function ($query) use ($type) {
                            $query->where('name', 'ilike', '%' . $type . '%');
                        });
                    })
                    ->skip(($page - 1) * $limit)
                    ->take($limit)
                    ->when($sort === 'created_at', function ($query) use ($sort, $order) {
                        $query->orderBy($sort, $order);
                    })
                    ->when($sort === 'rev', function ($query) use ($sort, $order) {
                        $query->orderBy($sort, $order);
                    })
                    ->paginate();
                foreach ($findAllCpiOrder as &$cpiOrder) {
                    if (isset($cpiOrder['section_approvals'])) {
                        $cpiOrder['approvals'] = $cpiOrder['section_approvals'];
                        unset($cpiOrder['section_approvals']);
                    }
                }
            }
        } else if ($user->stream_verifications) {
            if ($status == 'waiting') {
                $fullyApprovedCpiOrders = [];
                $findAllCpiOrder = CpiOrder::with([
                    'streams',
                    'sections',
                    'lines',
                    'documents' => function ($query) use ($sort, $order) {
                        $query->when($sort === 'name', function ($query) use ($sort, $order) {
                            $query->orderBy($sort, $order);
                        });
                    },
                    'verification_approvals' => function ($query) use ($user) {
                        $query->where('stream_verification_id', $user->stream_verifications['id']);
                    }
                ])
                    ->where(function ($query) use ($date) {
                        if (!empty($date)) {
                            $query->whereDate('cpi_orders.created_at', $date);
                        }
                    })
                    ->whereHas('verification_approvals', function ($query) {
                        $query
                            ->where('status', 'approved')->orWhere('status', 'declined');
                    })
                    ->where(function ($query) use ($search) {
                        $query->whereHas('sections', function ($query) use ($search) {
                            $query->where('name', 'ilike', '%' . $search . '%');
                        });
                    })
                    ->where(function ($query) use ($type) {
                        $query->whereHas('documents', function ($query) use ($type) {
                            $query->where('name', 'ilike', '%' . $type . '%');
                        });
                    })
                    ->skip(($page - 1) * $limit)
                    ->take($limit)
                    ->when($sort === 'created_at', function ($query) use ($sort, $order) {
                        $query->orderBy($sort, $order);
                    })
                    ->when($sort === 'rev', function ($query) use ($sort, $order) {
                        $query->orderBy($sort, $order);
                    })
                    ->paginate();

                foreach ($findAllCpiOrder as $cpiOrder) {
                    $allSectionApprovalsApproved = $cpiOrder->section_approvals->count() === $cpiOrder->section_approvals->where('status', 'approved')->count();

                    if ($allSectionApprovalsApproved) {
                        $fullyApprovedCpiOrders[] = $cpiOrder;
                    }
                }
                $currentPage = Paginator::resolveCurrentPage();
                $perPage = $limit;
                $offset = ($currentPage - 1) * $perPage;

                $fullyApprovedCpiOrders = array_slice($fullyApprovedCpiOrders, $offset, $perPage);
                $paginatedFullyApprovedCpiOrders = new LengthAwarePaginator($fullyApprovedCpiOrders, count($fullyApprovedCpiOrders), $perPage);

                $pagination = [
                    "page" => $paginatedFullyApprovedCpiOrders->currentPage(),
                    "limit" => $paginatedFullyApprovedCpiOrders->perPage(),
                    "search" => $search,
                    "totalRows" => $paginatedFullyApprovedCpiOrders->total(),
                    "totalPages" => $paginatedFullyApprovedCpiOrders->lastPage(),
                    "nextPage" => $paginatedFullyApprovedCpiOrders->nextPageUrl(),
                    "prevPage" => $paginatedFullyApprovedCpiOrders->previousPageUrl()
                ];
                foreach ($findAllCpiOrder as &$cpiOrder) {
                    if (isset($cpiOrder['verification_approvals'])) {
                        $cpiOrder['approvals'] = $cpiOrder['verification_approvals'];
                        unset($cpiOrder['verification_approvals']);
                    }
                }

                $data = $paginatedFullyApprovedCpiOrders->items();
                $response = [
                    "pagination" => $pagination,
                    "data" => $data
                ];

                return response()->json($response);
            } else if ($status == 'confirmed') {

                $findAllCpiOrder = CpiOrder::with([
                    'streams',
                    'sections',
                    'lines',
                    'documents' => function ($query) use ($sort, $order) {
                        $query->when($sort === 'name', function ($query) use ($sort, $order) {
                            $query->orderBy($sort, $order);
                        });
                    },
                    'verification_approvals' => function ($query) use ($user) {
                        $query->where('stream_verification_id', $user->stream_verifications['id']);
                    }
                ])
                    ->where(function ($query) use ($date) {
                        if (!empty($date)) {
                            $query->whereDate('cpi_orders.created_at', $date);
                        }
                    })
                    ->whereHas('verification_approvals', function ($query) {
                        $query
                            ->where('status', 'approved')->orWhere('status', 'declined');
                    })
                    ->where(function ($query) use ($search) {
                        $query->whereHas('sections', function ($query) use ($search) {
                            $query->where('name', 'ilike', '%' . $search . '%');
                        });
                    })
                    ->where(function ($query) use ($type) {
                        $query->whereHas('documents', function ($query) use ($type) {
                            $query->where('name', 'ilike', '%' . $type . '%');
                        });
                    })
                    ->skip(($page - 1) * $limit)
                    ->take($limit)
                    ->when($sort === 'created_at', function ($query) use ($sort, $order) {
                        $query->orderBy($sort, $order);
                    })
                    ->when($sort === 'rev', function ($query) use ($sort, $order) {
                        $query->orderBy($sort, $order);
                    })
                    ->paginate();

                foreach ($findAllCpiOrder as &$cpiOrder) {
                    if (isset($cpiOrder['verification_approvals'])) {
                        $cpiOrder['approvals'] = $cpiOrder['verification_approvals'];
                        unset($cpiOrder['verification_approvals']);
                    }
                }
            }
        }
        $pagination = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'totalRows' => $findAllCpiOrder->total(),
            'totalPages' => $findAllCpiOrder->lastPage(),
            'nextPage' => $findAllCpiOrder->nextPageUrl(),
            'prevPage' => $findAllCpiOrder->previousPageUrl(),
        ];
        $tableData = [
            'pagination' => $pagination,
            'data' => $findAllCpiOrder->items()
        ];

        return response()->json($tableData);
    }

    public function show($cpi_order_id)
    {
        $result_cpiOrderDoc = new \stdClass();
        $result_sectionApproval = [];
        $result_verificationApproval = [];

        $cpiOrderDoc = CpiOrder::with(['users', 'streams', 'documents'])->find($cpi_order_id);

        $result_cpiOrderDoc->id = $cpiOrderDoc->id;
        $result_cpiOrderDoc->rev = $cpiOrderDoc->rev;
        $result_cpiOrderDoc->status = $cpiOrderDoc->status;
        $result_cpiOrderDoc->user_name = $cpiOrderDoc->users->name;
        $result_cpiOrderDoc->stream_name = $cpiOrderDoc->streams->name;
        $result_cpiOrderDoc->document_name = $cpiOrderDoc->documents->name;
        $result_cpiOrderDoc->created_at = $cpiOrderDoc->created_at;

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
        ])
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

    public function approve(Request $request)
    {
        $user = JWTAuth::user();
        $cpiOrderId = $request['cpi_order_id'];

        $findSectionHeadApproval = SectionApproval::where('cpi_order_id', $cpiOrderId)->get();
        $findVerificationApproval = VerificationApproval::where('cpi_order_id', $cpiOrderId)->get();
        foreach ($findSectionHeadApproval as $sectionHeadApproval) {
            if ($sectionHeadApproval['status'] == 'declined') {
                return response()->json([
                    'message' => 'Section Head has already declined'
                ], 400);
            }
        }
        foreach ($findVerificationApproval as $verificationApproval) {
            if ($verificationApproval['status'] == 'declined') {
                return response()->json([
                    'message' => 'Verification has already declined'
                ], 400);
            }
        }

        try {
            $findCurrentStream = StreamSectionHead::where('user_id', $user->id)->first();
            if (!$findCurrentStream) {
                $findCurrentStream = StreamVerification::where('user_id', $user->id)->first();
                if (!$findCurrentStream) {
                    return response()->json([
                        'message' => 'You are not a stream section head or stream verification'
                    ], 400);
                }
                $findVerificationApproval = VerificationApproval::where('cpi_order_id', $cpiOrderId)
                    ->where('stream_verification_id', $findCurrentStream->id)
                    ->first();
                $findVerificationApproval->status = 'approved';
                $findVerificationApproval->updated_at = now();
                $findVerificationApproval->save();
            } else {
                $findSectionHeadApproval = SectionApproval::where('cpi_order_id', $cpiOrderId)
                    ->where('stream_section_head_id', $findCurrentStream->id)
                    ->first();
                $findSectionHeadApproval->status = 'approved';
                $findSectionHeadApproval->updated_at = now();
                $findSectionHeadApproval->save();
            }

            $countTotalSectionHead = SectionApproval::where('cpi_order_id', $cpiOrderId)
                ->count();
            $countTotalVerification = VerificationApproval::where('cpi_order_id', $cpiOrderId)->count();
            $countTotalSectionHeadApproved = SectionApproval::where('cpi_order_id', $cpiOrderId)->where('status', 'approved')->count();
            $countTotalVerificationApproved = VerificationApproval::where('cpi_order_id', $cpiOrderId)->where('status', 'approved')->count();

            if (($countTotalSectionHeadApproved / $countTotalSectionHead) == 1
                && ($countTotalVerificationApproved / $countTotalVerification) == 1
            ) {
                CpiOrder::where('cpi_order_id', $cpiOrderId)
                    ->update([
                        'status' => 'approved',
                        'updated_at' => now()
                    ]);
                $findCpiOrder = CpiOrder::find($cpiOrderId);
                Notification::create([
                    'cpi_order_id' => $cpiOrderId,
                    'user_id' => $findCpiOrder->user_id,
                    'status' => false,
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Cpi Order has been approved'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Internal Server Error",
                "error" => $e->getMessage()
            ]);
        }
    }

    public function declined(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cpi_order_id' => 'required|uuid',
            'reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = JWTAuth::user();
        $cpiOrderId = $request['cpi_order_id'];
        $findInspector = CpiOrder::with(['users'])->find($cpiOrderId);

        $findSectionHeadApproval = SectionApproval::where('cpi_order_id', $cpiOrderId)->get();
        $findVerificationApproval = VerificationApproval::where('cpi_order_id', $cpiOrderId)->get();
        foreach ($findSectionHeadApproval as $sectionHeadApproval) {
            if ($sectionHeadApproval['status'] == 'declined') {
                return response()->json([
                    'message' => 'Section Head has already declined'
                ], 400);
            }
        }
        foreach ($findVerificationApproval as $verificationApproval) {
            if ($verificationApproval['status'] == 'declined') {
                return response()->json([
                    'message' => 'Verification has already declined'
                ], 400);
            }
        }
        try {
            DB::beginTransaction();
            $findCurrentStream = StreamSectionHead::where('user_id', $user->id)->first();
            if (!$findCurrentStream) {
                $findCurrentStream = StreamVerification::where('user_id', $user->id)->first();
                if (!$findCurrentStream) {
                    return response()->json([
                        'message' => 'You are not a stream section head or stream verification'
                    ], 400);
                }
                $findVerificationApproval = VerificationApproval::where('cpi_order_id', $cpiOrderId)
                    ->where('stream_verification_id', $findCurrentStream->id)
                    ->first();
                $findVerificationApproval->status = 'declined';
                $findVerificationApproval->updated_at = now();
                $findVerificationApproval->save();
                $declinedDoc = DeclineReason::create([
                    'verification_approval_id' => $findVerificationApproval['id'],
                    'reason' => $request['reason']
                ]);
                LogTrailDeclined::create([
                    'cpi_order_id' => $cpiOrderId,
                    'declined_reason_id' => $declinedDoc['id'],
                    'change' => $findCurrentStream['name'],
                    'inspector' => $findInspector->users->name,
                    'declined_by' => $user->name,
                    'timestamp' => now()
                ]);
            } else {
                $findSectionHeadApproval = SectionApproval::where('cpi_order_id', $cpiOrderId)
                    ->where('stream_section_head_id', $findCurrentStream->id)
                    ->first();
                $findSectionHeadApproval->status = 'declined';
                $findSectionHeadApproval->updated_at = now();
                $findSectionHeadApproval->save();
                $declinedDoc = DeclineReason::create([
                    'section_approval_id' => $findSectionHeadApproval['id'],
                    'reason' => $request['reason']
                ]);
                LogTrailDeclined::create([
                    'cpi_order_id' => $cpiOrderId,
                    'declined_reason_id' => $declinedDoc['id'],
                    'change' => $findCurrentStream['name'],
                    'inspector' => $findInspector->users->name,
                    'declined_by' => $user->name,
                    'timestamp' => now()
                ]);
            }

            CpiOrder::where('id', $cpiOrderId)
                ->update([
                    'status' => 'declined',
                    'updated_at' => now()
                ]);
            $findCpiOrder = CpiOrder::find($cpiOrderId);
            Notification::create([
                'cpi_order_id' => $cpiOrderId,
                'user_id' => $findCpiOrder->user_id,
                'status' => false,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Cpi Order has been declined'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Internal Server Error",
                "error" => $e->getMessage()
            ]);
        }
    }
}
