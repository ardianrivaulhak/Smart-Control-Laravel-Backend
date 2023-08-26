<?php

namespace App\Http\Controllers\Web\MasterData;

use App\Models\ControlProcess;
use App\Models\Section;
use App\Models\Standard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ControlProcessStandard;
use App\Models\Document;
use App\Models\Form;
use App\Models\FormControlProcess;
use App\Models\LogTrail;
use App\Models\LogTrailDetail;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class FormController extends Controller
{

    public function index(Request $request)
    {
        # code...
        $search = $request->query('search', '');
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);
        $document = $request->query('type');

        $forms = FormControlProcess::with(['documents', 'sections', 'control_process_standards'])
            ->whereHas('documents', function ($query) use ($document) {
                $query->where('name', $document);
            })
            ->where(function ($query) use ($search) {
                $query->where('control_process_name', 'ilike', '%' . $search . '%')
                    ->orWhere(function ($query) use ($search) {
                        $query->whereHas('sections', function ($query) use ($search) {
                            $query->where('name', 'ilike', '%' . $search . '%');
                        });
                    });
            })
            ->paginate();


        $pagination = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'totalRows' => $forms->total(),
            'totalPages' => $forms->lastPage(),
            'nextPage' => $forms->nextPageUrl(),
            'prevPage' => $forms->previousPageUrl(),
        ];
        $tableData = [
            'pagination' => $pagination,
            'data' => $forms->items()
        ];
        return response()->json($tableData, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_id' => 'required|string',
            'forms' => 'required|array',
            'forms.*.id' => 'sometimes|required|string',
            'forms.*.section_id' => 'required|string',
            'forms.*.control_process_name' => 'required|string',
            'forms.*.standards' => 'required|array',
            'forms.*.standards.*.id' => 'sometimes|required|string',
            'forms.*.standards.*.name' => 'required|string',
            'deleted_forms' => 'array',
            'deleted_forms.form_ids' => 'array',
            'deleted_forms.form_ids.*' => 'string|uuid',
            'deleted_forms.standard_ids' => 'array',
            'deleted_forms.standard_ids.*' => 'string|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        $user = JWTAuth::user();
        $findDocument = Document::find($request->document_id);

        if (!$findDocument) {
            return response()->json(["message" => "Document not found"], 404);
        }

        try {
            DB::beginTransaction();

            foreach ($request->forms as $form) {

                if (!isset($form['id'])) {
                    $createFormControlProcess = FormControlProcess::create([
                        'document_id' => $request->document_id,
                        'section_id' => $form['section_id'],
                        'control_process_name' => $form['control_process_name'],
                    ]);
                    foreach ($form['standards'] as $standard) {
                        $createdControlProcessStandard = ControlProcessStandard::create([
                            'form_control_process_id' => $createFormControlProcess->id,
                            'name' => $standard['name']
                        ]);
                    }
                } else {
                    $existingForm = FormControlProcess::find($form['id']);
                    if ($existingForm) {
                        $findLatestRevLogTrails = LogTrail::where('document_id', $request->document_id)
                            ->orderBy('rev', 'desc')->first();
                        $createdLogTrail = null;
                        if ($existingForm['control_process_name'] != $form['control_process_name']) {
                            $createdLogTrail = LogTrail::create([
                                'document_id' => $request->document_id,
                                'timestamp' => now(),
                                'rev' => !$findLatestRevLogTrails ? 0 : $findLatestRevLogTrails->rev + 1,
                                'changed_by' => $user->name,
                            ]);
                            LogTrailDetail::create([
                                'log_trail_id' => $createdLogTrail->id,
                                'change' => $form['control_process_name'],
                            ]);
                        }
                        $existingForm->update([
                            'section_id' => $form['section_id'],
                            'control_process_name' => $form['control_process_name'],
                            'updated_at' => now(),
                        ]);
                        foreach ($form['standards'] as $standard) {
                            if (isset($standard['id'])) {
                                $existingStandard = ControlProcessStandard::find($standard['id']);
                                if (!$existingStandard) {
                                    return response()->json(["message" => "Standard not found"], 404);
                                }
                                if ($existingStandard['name'] != $standard['name']) {
                                    if (is_null($createdLogTrail)) {
                                        $createdLogTrail = LogTrail::create([
                                            'document_id' => $request->document_id,
                                            'timestamp' => now(),
                                            'rev' => !$findLatestRevLogTrails ? 0 : $findLatestRevLogTrails->rev + 1,
                                            'changed_by' => $user->name,
                                        ]);
                                        LogTrailDetail::create([
                                            'log_trail_id' => $createdLogTrail->id,
                                            'change' => $standard['name'],
                                        ]);
                                    } else {
                                        LogTrailDetail::create([
                                            'log_trail_id' => $createdLogTrail->id,
                                            'change' => $standard['name'],
                                        ]);
                                    }
                                }
                                $existingStandard->update([
                                    'name' => $standard['name'],
                                    'updated_at' => now(),
                                ]);
                            } else {
                                ControlProcessStandard::create([
                                    'form_control_process_id' => $form['id'],
                                    'name' => $standard['name']
                                ]);
                            }
                        }
                    }
                }
            }
            if (!empty($request->deleted_forms['standard_ids'])) {
                foreach ($request->deleted_forms['standard_ids'] as $standardId) {
                    ControlProcessStandard::find($standardId)->delete();
                }
            }
            if (!empty($request->deleted_forms['form_ids'])) {
                foreach ($request->deleted_forms['form_ids'] as $formId) {
                    FormControlProcess::find($formId)->delete();
                }
            }
            DB::commit();

            return response()->json(['message' => 'Form successfully updated'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Internal Server Error",
                "error" => $e->getMessage()
            ]);
        }
    }

    public function logTrails(Request $request)
    {

        $search = $request->query('search', '');
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);
        $type = $request->query('type');
        $sort = $request->query('sort', 'created_at');
        $order = $request->query('order', 'desc');

        $logTrails = LogTrail::with(['log_trail_details'])
            ->whereHas('documents', function ($query) use ($type) {
                $query->where('name', $type);
            })
            ->where(function ($query) use ($search) {
                $query->where('changed_by', 'ilike', '%' . $search . '%')
                    ->orWhere(function ($query) use ($search) {
                        $query->whereHas('log_trail_details', function ($query) use ($search) {
                            $query->where('change', 'ilike', '%' . $search . '%');
                        });
                    });
            })
            ->orderBy($sort, $order)
            ->paginate();

        $pagination = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'totalRows' => $logTrails->total(),
            'totalPages' => $logTrails->lastPage(),
            'nextPage' => $logTrails->nextPageUrl(),
            'prevPage' => $logTrails->previousPageUrl(),
        ];
        $tableData = [
            'pagination' => $pagination,
            'data' => $logTrails->items()
        ];

        return response()->json($tableData, 200);
    }
}
