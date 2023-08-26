<?php

namespace App\Http\Controllers\Mobile\CpiOrder;

use App\Models\CpiOrder;
use App\Models\CpiOrderHasSection;
use App\Models\CpiOrderHasStandard;
use App\Models\Document;
use App\Models\FormControlProcess;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Models\CpiOrderExit;
use App\Models\CpiOrderHasControlProcess;
use App\Models\CpiOrderHasControlProcessPhoto;
use App\Models\CpiOrderHasProblem;
use App\Models\CpiOrderHasSampling;
use App\Models\DeclineReason;
use App\Models\IsCpiOrderCorrected;
use App\Models\Problem;
use App\Models\Sampling;
use App\Models\SectionApproval;
use App\Models\Stream;
use App\Models\StreamSectionHead;
use App\Models\StreamVerification;
use App\Models\Verification;
use App\Models\VerificationApproval;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class CpiOrderController extends Controller
{
    //
    public function index(Request $request)
    {
        $documentId = $request->query('document_id');
        $sectionId = $request->query('section_id');

        $section = Section::with(['form_control_process' => function ($query) use ($documentId) {
            $query->with(['control_process_standards'])
                ->where('document_id', $documentId);
        }])
            ->whereIn('id', $sectionId)
            ->whereHas('form_control_process')
            ->get();

        return response()->json($section);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stream_id' => 'required|string',
            'document_id' => 'required|string',
            'cpi_order_id' => 'string',
            'forms' => 'required|array',
            'forms.*.section_id' => 'required|string',
            'forms.*.line_id' => 'required|string',
            'forms.*.section' => 'required|array',
            'forms.*.section.*.form_control_process' => 'required|array',
            'forms.*.section.*.form_control_process.id' => 'required|string',
            'forms.*.section.*.form_control_process.standards' => 'required|array',
            'forms.*.section.*.form_control_process.standards.*.id' => 'required|string',
            'forms.*.section.*.form_control_process.standards.*.status' => 'required|boolean',
            'forms.*.section.*.form_control_process.standards.*.description' => 'required|string',
            'forms.*.section.*.form_control_process.control_process_photos' => 'required|array|sometimes',
            'forms.*.section.*.form_control_process.control_process_photos.*.id' => 'required|string|sometimes',
            'forms.*.section.*.form_control_process.control_process_photos.*.photo_url' => 'required|string|sometimes',
            'problems' => 'array',
            'problems.*.name' => 'string',
            'problems.*.part_name' => 'string',
            'problems.*.type_name' => 'string',
            'problems.*.lot' => 'integer',
            'problems.*.reason' => 'string',
            'problems.*.action' => 'string',
            'problems.*.reject' => 'integer',
            'problems.*.ng' => 'integer',
            'problems.*.ok' => 'integer',
            'problems.*.identity' => 'integer',
            'samplings' => 'array',
            'samplings.*.name' => 'string',
            'samplings.*.type_name' => 'string',
            'samplings.*.std' => 'integer',
            'samplings.*.rh' => 'integer',
            'samplings.*.lh' => 'integer',
            'samplings.*.judgement' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        $photoUrls = [];
        $user = JWTAuth::user();
        $findDocument = Document::find($request->document_id);

        if (!$findDocument) {
            return response()->json(["message" => "Document not found"], 404);
        }

        try {
            DB::beginTransaction();

            if (!$request['cpi_order_id']) {
                $createdCpiOrder = CpiOrder::create([
                    'stream_id' => $request['stream_id'],
                    'user_id' => $user['id'],
                    'document_id' => $request['document_id'],
                    'status' => 'waiting',
                    'rev' => 0
                ]);
                foreach ($request['forms'] as $form) {
                    CpiOrderHasSection::create([
                        'cpi_order_id' => $createdCpiOrder->id,
                        'section_id' => $form['section_id'],
                        'line_id' => $form['line_id'],
                    ]);
                    foreach ($form['section'] as $section) {
                        $createdCpiOrderHasControlProcess = CpiOrderHasControlProcess::create([
                            'cpi_order_id' => $createdCpiOrder->id,
                            'form_control_process_id' => $section['form_control_process']['id'],
                        ]);
                        foreach ($section['form_control_process']['standards'] as $standard) {
                            CpiOrderHasStandard::create([
                                'cpi_order_id' => $createdCpiOrder->id,
                                'control_process_standard_id' => $standard['id'],
                                'status' => $standard['status'],
                                'description' => $standard['description']
                            ]);
                        }
                        if (!empty($section['form_control_process']['control_process_photos'])) {
                            foreach ($section['form_control_process']['control_process_photos'] as $photo) {
                                $sourcePath = storage_path('app/' . str_replace('storage', 'public', $photo['photo_url']));
                                $fileName = pathinfo($sourcePath)['basename'];
                                $destinationPath = storage_path('app/public/uploads/' . $fileName);
                                if (file_exists($sourcePath)) {
                                    $photoUrls[] = $destinationPath;
                                    File::move($sourcePath, $destinationPath);
                                }

                                $photo['photo_url'] = 'storage/uploads/' . $fileName;
                                CpiOrderHasControlProcessPhoto::create([
                                    'cpi_order_has_control_process_id' => $createdCpiOrderHasControlProcess->id,
                                    'photo_url' => $photo['photo_url'],
                                ]);
                            }
                        }
                    }
                }
                if (!empty($request['problems'])) {
                    foreach ($request['problems'] as $problem) {
                        $createdProblem = Problem::create([
                            'name' => $problem['name'],
                            'part_name' => $problem['part_name'],
                            'type_name' => $problem['type_name'],
                            'lot' => $problem['lot'],
                            'reason' => $problem['reason'],
                            'action' => $problem['action'],
                            'reject' => $problem['reject'],
                            'ng' => $problem['ng'],
                            'ok' => $problem['ok'],
                            'identity' => $problem['identity']
                        ]);
                        CpiOrderHasProblem::create([
                            'cpi_order_id' => $createdCpiOrder->id,
                            'problem_id' => $createdProblem->id
                        ]);
                    }
                }
                if (!empty($request['samplings'])) {
                    foreach ($request['samplings'] as $sampling) {
                        $createdSampling = Sampling::create([
                            'name' => $sampling['name'],
                            'type_name' => $sampling['type_name'],
                            'std' => $sampling['std'],
                            'rh' => $sampling['rh'],
                            'lh' => $sampling['lh'],
                            'judgement' => $sampling['judgement']
                        ]);
                        CpiOrderHasSampling::create([
                            'cpi_order_id' => $createdCpiOrder->id,
                            'sampling_id' => $createdSampling->id
                        ]);
                    }
                }
                $findStreamSectionHead = StreamSectionHead::with([
                    'stream',
                    'stream.cpi_orders' => function ($query) use ($createdCpiOrder) {
                        $query->where('id', $createdCpiOrder->id);
                    }
                ])->get();
                $findStreamVerification = StreamVerification::with([
                    'stream',
                    'stream.cpi_orders' => function ($query) use ($createdCpiOrder) {
                        $query->where('id', $createdCpiOrder->id);
                    }
                ])->get();

                foreach ($findStreamSectionHead as $streamSectionHead) {
                    SectionApproval::create([
                        'cpi_order_id' => $createdCpiOrder->id,
                        'stream_section_head_id' => $streamSectionHead['id'],
                        'status' => 'waiting'
                    ]);
                }

                foreach ($findStreamVerification as $streamVerification) {
                    VerificationApproval::create([
                        'cpi_order_id' => $createdCpiOrder->id,
                        'stream_verification_id' => $streamVerification['id'],
                        'status' => 'waiting'
                    ]);
                }
            } else {
                $findCpiOrder = CpiOrder::find($request['cpi_order_id']);
                if ($findCpiOrder['status'] == 'waiting') {
                    $cpiOrderControlProcesses = CpiOrderHasControlProcess::with(['cpi_order_has_control_process_photos'])->get();
                    foreach ($cpiOrderControlProcesses as $cpiOrderControlProcess) {
                        foreach ($cpiOrderControlProcess['cpi_order_has_control_process_photos'] as $photo) {
                            $sourcePath = storage_path('app/' . str_replace('storage', 'public', $photo['photo_url']));
                            if (File::exists($sourcePath)) {
                                File::delete($sourcePath);
                            }
                        }
                    }
                    CpiOrderExit::where('cpi_order_id', $findCpiOrder['id'])->forceDelete();
                    CpiOrderHasSection::where('cpi_order_id', $findCpiOrder['id'])->forceDelete();
                    CpiOrderHasStandard::where('cpi_order_id', $findCpiOrder['id'])->forceDelete();
                    CpiOrderHasControlProcessPhoto::with(['cpi_order_has_control_process' => function ($query) use ($findCpiOrder) {
                        $query->where('cpi_order_id', $findCpiOrder['id']);
                    }])->forceDelete();
                    CpiOrderHasControlProcess::where('cpi_order_id', $findCpiOrder['id'])->forceDelete();
                    Problem::with(['cpi_orders' => function ($query) use ($findCpiOrder) {
                        $query->where('id', $findCpiOrder['id']);
                    }])->forceDelete();
                    Sampling::with(['cpi_orders' => function ($query) use ($findCpiOrder) {
                        $query->where('id', $findCpiOrder['id']);
                    }])->forceDelete();
                    CpiOrderHasProblem::where('cpi_order_id', $findCpiOrder['id'])->forceDelete();
                    CpiOrderHasSampling::where('cpi_order_id', $findCpiOrder['id'])->forceDelete();
                    foreach ($request['forms'] as $form) {
                        CpiOrderHasSection::create([
                            'cpi_order_id' => $request['cpi_order_id'],
                            'section_id' => $form['section_id'],
                            'line_id' => $form['line_id'],
                        ]);
                        foreach ($form['section'] as $section) {
                            $createdCpiOrderHasControlProcess = CpiOrderHasControlProcess::create([
                                'cpi_order_id' => $request['cpi_order_id'],
                                'form_control_process_id' => $section['form_control_process']['id'],
                            ]);
                            foreach ($section['form_control_process']['standards'] as $standard) {
                                CpiOrderHasStandard::create([
                                    'cpi_order_id' => $request['cpi_order_id'],
                                    'control_process_standard_id' => $standard['id'],
                                    'status' => $standard['status'],
                                    'description' => $standard['description']
                                ]);
                            }
                            if (!empty($section['form_control_process']['control_process_photos'])) {
                                foreach ($section['form_control_process']['control_process_photos'] as $photo) {
                                    $sourcePath = storage_path('app/' . str_replace('storage', 'public', $photo['photo_url']));
                                    $fileName = pathinfo($sourcePath)['basename'];
                                    $destinationPath = storage_path('app/public/uploads/' . $fileName);
                                    if (File::exists($sourcePath)) {
                                        File::move($sourcePath, $destinationPath);
                                    }
                                    $photo['photo_url'] = 'storage/uploads/' . $fileName;
                                    CpiOrderHasControlProcessPhoto::create([
                                        'cpi_order_has_control_process_id' => $createdCpiOrderHasControlProcess->id,
                                        'photo_url' => $photo['photo_url'],
                                    ]);
                                }
                            }
                        }
                    }
                    if (!empty($request['problems'])) {
                        foreach ($request['problems'] as $problem) {
                            $createdProblem = Problem::create([
                                'name' => $problem['name'],
                                'part_name' => $problem['part_name'],
                                'type_name' => $problem['type_name'],
                                'lot' => $problem['lot'],
                                'reason' => $problem['reason'],
                                'action' => $problem['action'],
                                'reject' => $problem['reject'],
                                'ng' => $problem['ng'],
                                'ok' => $problem['ok'],
                                'identity' => $problem['identity']
                            ]);
                            CpiOrderHasProblem::create([
                                'cpi_order_id' => $findCpiOrder->id,
                                'problem_id' => $createdProblem->id
                            ]);
                        }
                    }
                    if (!empty($request['samplings'])) {
                        foreach ($request['samplings'] as $sampling) {
                            $createdSampling = Sampling::create([
                                'name' => $sampling['name'],
                                'type_name' => $sampling['type_name'],
                                'std' => $sampling['std'],
                                'rh' => $sampling['rh'],
                                'lh' => $sampling['lh'],
                                'judgement' => $sampling['judgement']
                            ]);
                            CpiOrderHasSampling::create([
                                'cpi_order_id' => $findCpiOrder->id,
                                'sampling_id' => $createdSampling->id
                            ]);
                        }
                    }

                    $findStreamSectionHead = StreamSectionHead::with([
                        'stream',
                        'stream.cpi_orders' => function ($query) use ($findCpiOrder) {
                            $query->where('id', $findCpiOrder->id);
                        }
                    ])->get();
                    $findStreamVerification = StreamVerification::with([
                        'stream',
                        'stream.cpi_orders' => function ($query) use ($findCpiOrder) {
                            $query->where('id', $findCpiOrder->id);
                        }
                    ])->get();

                    foreach ($findStreamSectionHead as $streamSectionHead) {
                        SectionApproval::create([
                            'cpi_order_id' => $findCpiOrder->id,
                            'stream_section_head_id' => $streamSectionHead['id'],
                            'status' => 'waiting'
                        ]);
                    }

                    foreach ($findStreamVerification as $streamVerification) {
                        VerificationApproval::create([
                            'cpi_order_id' => $findCpiOrder->id,
                            'stream_verification_id' => $streamVerification['id'],
                            'status' => 'waiting'
                        ]);
                    }
                } else if ($findCpiOrder['status'] == 'declined') {
                    $createdCpiOrder = CpiOrder::create([
                        'stream_id' => $request['stream_id'],
                        'user_id' => $user['id'],
                        'document_id' => $request['document_id'],
                        'status' => 'waiting',
                        'rev' => $findCpiOrder['rev'] + 1
                    ]);
                    foreach ($request['forms'] as $form) {
                        CpiOrderHasSection::create([
                            'cpi_order_id' => $createdCpiOrder->id,
                            'section_id' => $form['section_id'],
                            'line_id' => $form['line_id'],
                        ]);
                        foreach ($form['section'] as $section) {
                            $createdCpiOrderHasControlProcess = CpiOrderHasControlProcess::create([
                                'cpi_order_id' => $createdCpiOrder->id,
                                'form_control_process_id' => $section['form_control_process']['id'],
                            ]);
                            foreach ($section['form_control_process']['standards'] as $standard) {
                                CpiOrderHasStandard::create([
                                    'cpi_order_id' => $createdCpiOrder->id,
                                    'control_process_standard_id' => $standard['id'],
                                    'status' => $standard['status'],
                                    'description' => $standard['description']
                                ]);
                            }
                            if (!empty($section['form_control_process']['control_process_photos'])) {
                                foreach ($section['form_control_process']['control_process_photos'] as $photo) {
                                    $sourcePath = storage_path('app/' . str_replace('storage', 'public', $photo['photo_url']));
                                    $fileName = pathinfo($sourcePath)['basename'];
                                    $destinationPath = storage_path('app/public/uploads/' . $fileName);
                                    if (File::exists($sourcePath)) {
                                        $photoUrls[] = $destinationPath;
                                        File::move($sourcePath, $destinationPath);
                                    }
                                    $photo['photo_url'] = 'storage/uploads/' . $fileName;
                                    CpiOrderHasControlProcessPhoto::create([
                                        'cpi_order_has_control_process_id' => $createdCpiOrderHasControlProcess->id,
                                        'photo_url' => $photo['photo_url'],
                                    ]);
                                }
                            }
                        }
                    }
                    if (!empty($request['problems'])) {
                        foreach ($request['problems'] as $problem) {
                            $createdProblem = Problem::create([
                                'name' => $problem['name'],
                                'part_name' => $problem['part_name'],
                                'type_name' => $problem['type_name'],
                                'lot' => $problem['lot'],
                                'reason' => $problem['reason'],
                                'action' => $problem['action'],
                                'reject' => $problem['reject'],
                                'ng' => $problem['ng'],
                                'ok' => $problem['ok'],
                                'identity' => $problem['identity']
                            ]);
                            CpiOrderHasProblem::create([
                                'cpi_order_id' => $createdCpiOrder->id,
                                'problem_id' => $createdProblem->id
                            ]);
                        }
                    }
                    if (!empty($request['samplings'])) {
                        foreach ($request['samplings'] as $sampling) {
                            $createdSampling = Sampling::create([
                                'name' => $sampling['name'],
                                'type_name' => $sampling['type_name'],
                                'std' => $sampling['std'],
                                'rh' => $sampling['rh'],
                                'lh' => $sampling['lh'],
                                'judgement' => $sampling['judgement']
                            ]);
                            CpiOrderHasSampling::create([
                                'cpi_order_id' => $createdCpiOrder->id,
                                'sampling_id' => $createdSampling->id
                            ]);
                        }
                    }

                    $findStreamSectionHead = SectionApproval::where('cpi_order_id', $findCpiOrder->id)->get();
                    $findStreamVerification = VerificationApproval::where('cpi_order_id', $findCpiOrder->id)->get();

                    foreach ($findStreamSectionHead as $streamSectionHead) {
                        if ($streamSectionHead != 'approved') {
                            SectionApproval::create([
                                'cpi_order_id' => $createdCpiOrder->id,
                                'stream_section_head_id' => $streamSectionHead->stream_section_head_id,
                                'status' => 'waiting'
                            ]);
                        } else {
                            SectionApproval::create([
                                'cpi_order_id' => $createdCpiOrder->id,
                                'stream_section_head_id' => $streamSectionHead->stream_section_head_id,
                                'status' => 'approved'
                            ]);
                        }
                    }

                    foreach ($findStreamVerification as $streamVerification) {
                        if ($streamVerification != 'approved') {
                            VerificationApproval::create([
                                'cpi_order_id' => $createdCpiOrder->id,
                                'stream_verification_id' => $streamVerification->stream_verification_id,
                                'status' => 'waiting'
                            ]);
                        } else {
                            VerificationApproval::create([
                                'cpi_order_id' => $createdCpiOrder->id,
                                'stream_verification_id' => $streamVerification->stream_verification_id,
                                'status' => 'approved'
                            ]);
                        }
                    }

                    IsCpiOrderCorrected::create([
                        'cpi_order_before_id' => $findCpiOrder->id,
                        'cpi_order_after_id' => $createdCpiOrder->id,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'CpiOrder created successfully'
            ], 201);
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
                $query->with(['control_process_standards.cpi_order_has_standards' => function ($query) use ($cpi_order_id) {
                    $query->where('cpi_order_id', $cpi_order_id);
                }, 'cpi_order_has_control_process' => function ($query) use ($cpi_order_id) {
                    $query
                        ->with(['cpi_order_has_control_process_photos'])
                        ->where('cpi_order_id', $cpi_order_id);
                }]);
            },
            'cpi_order_has_sections.lines',
            'samplings',
            'problems',
        ])
            ->where('user_id', $user['id'])
            ->find($cpi_order_id);

        if (!$cpiOrder) {
            return response()->json([
                'message' => 'CpiOrder not found'
            ], 404);
        }

        return response()->json([
            "message" => "CpiOrder found",
            "data" => $cpiOrder
        ]);
    }

    public function exit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stream_id' => 'required|string',
            'document_id' => 'required|string',
            'reason' => 'required|string',
            'cpi_order_id' => 'string',
            'forms' => 'array',
            'forms.*.section_id' => 'string|required',
            'forms.*.line_id' => 'string|required',
            'forms.*.section' => 'array',
            'forms.*.section.*.form_control_process' => 'array',
            'forms.*.section.*.form_control_process.id' => 'string',
            'forms.*.section.*.form_control_process.standards' => 'array',
            'forms.*.section.*.form_control_process.standards.*.id' => 'string',
            'forms.*.section.*.form_control_process.standards.*.status' => 'boolean',
            'forms.*.section.*.form_control_process.standards.*.description' => 'string',
            'forms.*.section.*.form_control_process.control_process_photos' => 'array',
            'forms.*.section.*.form_control_process.control_process_photos.*.id' => 'string',
            'forms.*.section.*.form_control_process.control_process_photos.*.photo_url' => 'string',
            'problems' => 'array',
            'problems.*.name' => 'string',
            'problems.*.part_name' => 'string',
            'problems.*.type_name' => 'string',
            'problems.*.lot' => 'integer',
            'problems.*.reason' => 'string',
            'problems.*.action' => 'string',
            'problems.*.reject' => 'integer',
            'problems.*.ng' => 'integer',
            'problems.*.ok' => 'integer',
            'problems.*.identity' => 'integer',
            'samplings' => 'array',
            'samplings.*.name' => 'string',
            'samplings.*.type_name' => 'string',
            'samplings.*.std' => 'integer',
            'samplings.*.rh' => 'integer',
            'samplings.*.lh' => 'integer',
            'samplings.*.judgement' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $photoUrls = [];
        $user = JWTAuth::user();
        $findDocument = Document::find($request->document_id);

        if (!$findDocument) {
            return response()->json(["message" => "Document not found"], 404);
        }

        try {
            DB::beginTransaction();

            if (!$request['cpi_order_id']) {
                $createdCpiOrder = CpiOrder::create([
                    'stream_id' => $request['stream_id'],
                    'user_id' => $user['id'],
                    'document_id' => $request['document_id'],
                    'status' => 'waiting',
                    'rev' => 0
                ]);
                CpiOrderExit::create([
                    'cpi_order_id' => $createdCpiOrder->id,
                    'reason' => $request->reason
                ]);
                if (!empty($request['forms'])) {
                    foreach ($request['forms'] as $form) {
                        CpiOrderHasSection::create([
                            'cpi_order_id' => $createdCpiOrder->id,
                            'section_id' => $form['section_id'],
                            'line_id' => $form['line_id'],
                        ]);
                        if (!empty($form['section'])) {
                            foreach ($form['section'] as $section) {
                                $createdCpiOrderHasControlProcess = CpiOrderHasControlProcess::create([
                                    'cpi_order_id' => $createdCpiOrder->id,
                                    'form_control_process_id' => $section['form_control_process']['id'],
                                ]);
                                foreach ($section['form_control_process']['standards'] as $standard) {
                                    CpiOrderHasStandard::create([
                                        'cpi_order_id' => $createdCpiOrder->id,
                                        'control_process_standard_id' => $standard['id'],
                                        'status' => $standard['status'],
                                        'description' => $standard['description']
                                    ]);
                                }
                                if (!empty($section['form_control_process']['control_process_photos'])) {
                                    foreach ($section['form_control_process']['control_process_photos'] as $photo) {
                                        $sourcePath = storage_path('app/' . str_replace('storage', 'public', $photo['photo_url']));
                                        $fileName = pathinfo($sourcePath)['basename'];
                                        $destinationPath = storage_path('app/public/uploads/' . $fileName);

                                        if (File::exists($sourcePath)) {
                                            File::move($sourcePath, $destinationPath);
                                        } else {
                                            DB::rollBack();
                                            return response()->json(["message" => "File not found"], 404);
                                        }

                                        $photo['photo_url'] = 'storage/uploads/' . $fileName;
                                        CpiOrderHasControlProcessPhoto::create([
                                            'cpi_order_has_control_process_id' => $createdCpiOrderHasControlProcess->id,
                                            'photo_url' => $photo['photo_url'],
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($request['problems'])) {
                    foreach ($request['problems'] as $problem) {
                        $createdProblem = Problem::create([
                            'name' => $problem['name'],
                            'part_name' => $problem['part_name'],
                            'type_name' => $problem['type_name'],
                            'lot' => $problem['lot'],
                            'reason' => $problem['reason'],
                            'action' => $problem['action'],
                            'reject' => $problem['reject'],
                            'ng' => $problem['ng'],
                            'ok' => $problem['ok'],
                            'identity' => $problem['identity']
                        ]);
                        CpiOrderHasProblem::create([
                            'cpi_order_id' => $createdCpiOrder->id,
                            'problem_id' => $createdProblem->id
                        ]);
                    }
                }
                if (!empty($request['samplings'])) {
                    foreach ($request['samplings'] as $sampling) {
                        $createdSampling = Sampling::create([
                            'name' => $sampling['name'],
                            'type_name' => $sampling['type_name'],
                            'std' => $sampling['std'],
                            'rh' => $sampling['rh'],
                            'lh' => $sampling['lh'],
                            'judgement' => $sampling['judgement']
                        ]);
                        CpiOrderHasSampling::create([
                            'cpi_order_id' => $createdCpiOrder->id,
                            'sampling_id' => $createdSampling->id
                        ]);
                    }
                }
            } else {
                $cpiOrder = CpiOrder::find($request['cpi_order_id']);

                $cpiOrderControlProcesses = CpiOrderHasControlProcess::with(['cpi_order_has_control_process_photos'])->get();
                foreach ($cpiOrderControlProcesses as $cpiOrderControlProcess) {
                    foreach ($cpiOrderControlProcess['cpi_order_has_control_process_photos'] as $photo) {
                        $sourcePath = storage_path('app/' . str_replace('storage', 'public', $photo['photo_url']));
                        if (File::exists($sourcePath)) {
                            File::delete($sourcePath);
                        }
                    }
                }
                CpiOrderExit::where('cpi_order_id', $cpiOrder['id'])->forceDelete();
                CpiOrderHasSection::where('cpi_order_id', $cpiOrder['id'])->forceDelete();
                CpiOrderHasStandard::where('cpi_order_id', $cpiOrder['id'])->forceDelete();
                CpiOrderHasControlProcessPhoto::with(['cpi_order_has_control_process' => function ($query) use ($cpiOrder) {
                    $query->where('cpi_order_id', $cpiOrder['id']);
                }])->forceDelete();
                CpiOrderHasControlProcess::where('cpi_order_id', $cpiOrder['id'])->forceDelete();
                Problem::with(['cpi_orders' => function ($query) use ($cpiOrder) {
                    $query->where('id', $cpiOrder['id']);
                }])->forceDelete();
                Sampling::with(['cpi_orders' => function ($query) use ($cpiOrder) {
                    $query->where('id', $cpiOrder['id']);
                }])->forceDelete();
                CpiOrderHasProblem::where('cpi_order_id', $cpiOrder['id'])->forceDelete();
                CpiOrderHasSampling::where('cpi_order_id', $cpiOrder['id'])->forceDelete();

                if (!empty($request['forms'])) {
                    foreach ($request['forms'] as $form) {
                        CpiOrderHasSection::create([
                            'cpi_order_id' => $cpiOrder->id,
                            'section_id' => $form['section_id'],
                            'line_id' => $form['line_id'],
                        ]);
                        if (!empty($form['section'])) {
                            foreach ($form['section'] as $section) {
                                $createdCpiOrderHasControlProcess = CpiOrderHasControlProcess::create([
                                    'cpi_order_id' => $cpiOrder->id,
                                    'form_control_process_id' => $section['form_control_process']['id'],
                                ]);
                                foreach ($section['form_control_process']['standards'] as $standard) {
                                    CpiOrderHasStandard::create([
                                        'cpi_order_id' => $cpiOrder->id,
                                        'control_process_standard_id' => $standard['id'],
                                        'status' => $standard['status'],
                                        'description' => $standard['description']
                                    ]);
                                }
                                if (!empty($section['form_control_process']['control_process_photos'])) {
                                    foreach ($section['form_control_process']['control_process_photos'] as $photo) {
                                        $sourcePath = storage_path('app/' . str_replace('storage', 'public', $photo['photo_url']));
                                        $fileName = pathinfo($sourcePath)['basename'];
                                        $destinationPath = storage_path('app/public/uploads/' . $fileName);

                                        if (File::exists($sourcePath)) {
                                            File::move($sourcePath, $destinationPath);
                                        } else {
                                            DB::rollBack();
                                            return response()->json(["message" => "File not found"], 404);
                                        }

                                        $photo['photo_url'] = 'storage/uploads/' . $fileName;
                                        CpiOrderHasControlProcessPhoto::create([
                                            'cpi_order_has_control_process_id' => $createdCpiOrderHasControlProcess->id,
                                            'photo_url' => $photo['photo_url'],
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($request['problems'])) {
                    foreach ($request['problems'] as $problem) {
                        $createdProblem = Problem::create([
                            'name' => $problem['name'],
                            'part_name' => $problem['part_name'],
                            'type_name' => $problem['type_name'],
                            'lot' => $problem['lot'],
                            'reason' => $problem['reason'],
                            'action' => $problem['action'],
                            'reject' => $problem['reject'],
                            'ng' => $problem['ng'],
                            'ok' => $problem['ok'],
                            'identity' => $problem['identity']
                        ]);
                        CpiOrderHasProblem::create([
                            'cpi_order_id' => $cpiOrder->id,
                            'problem_id' => $createdProblem->id
                        ]);
                    }
                }
                if (!empty($request['samplings'])) {
                    foreach ($request['samplings'] as $sampling) {
                        $createdSampling = Sampling::create([
                            'name' => $sampling['name'],
                            'type_name' => $sampling['type_name'],
                            'std' => $sampling['std'],
                            'rh' => $sampling['rh'],
                            'lh' => $sampling['lh'],
                            'judgement' => $sampling['judgement']
                        ]);
                        CpiOrderHasSampling::create([
                            'cpi_order_id' => $cpiOrder->id,
                            'sampling_id' => $createdSampling->id
                        ]);
                    }
                }
            }
            DB::commit();
            return response()->json([
                'message' => 'Cpi Order saved',
            ], 200);
        } catch (\Exception $e) {
            if (!empty($photoUrls)) {
                foreach ($photoUrls as $photoUrl) {
                    $fileName = pathinfo($photoUrl)['basename'];
                    $destinationPath = storage_path('app/public/temp/' . $fileName);
                    File::move($photoUrl, $destinationPath);
                }
            }
            DB::rollBack();
            return response()->json([
                "message" => "Internal Server Error",
                "error" => $e->getMessage()
            ]);
        }
    }
}
