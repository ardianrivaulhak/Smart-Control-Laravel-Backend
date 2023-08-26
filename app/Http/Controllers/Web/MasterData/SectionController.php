<?php

namespace App\Http\Controllers\Web\MasterData;

use App\Models\Line;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SectionController extends Controller
{
    //

    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);
        $sort = $request->query('sort', 'created_at');
        $order = $request->query('order', 'desc');

        $sections = Section::with(['lines'])
            ->where('name', 'ilike', '%' . $search . '%')
            ->whereHas('lines')
            ->select('id', 'name')
            ->orderBy($sort, $order)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->paginate($limit);

        $pagination = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'totalRows' => $sections->total(),
            'totalPages' => $sections->lastPage(),
            'nextPage' => $sections->nextPageUrl(),
            'prevPage' => $sections->previousPageUrl(),
        ];
        $tableData = [
            'pagination' => $pagination,
            'data' => $sections->items()
        ];

        return response()->json($tableData, 200);
    }

    public function listForParams()
    {
        $streams = Section::select('id', 'name')
            ->with(['lines'])
            ->whereHas('lines')
            ->get();

        return response()->json([
            'message' => 'Success',
            'data' => $streams
        ], 200);
    }

    public function show($section_id)
    {
        $section = Section::with(['lines'])->where('id', $section_id)->first();

        if (!$section) {
            return response()->json([
                "message" => "Section not found"
            ]);
        }

        return response()->json($section, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'line_names' => 'array|required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            DB::beginTransaction();
            $section = Section::create([
                'id' => Str::uuid()->toString(),
                "name" => $request->name,
                "created_at" => now(),
                "updated_at" => now(),
                "deleted_at" => null,
            ]);

            foreach ($request->line_names as $lineName) {
                Line::create([
                    'id' => Str::uuid()->toString(),
                    "section_id" => $section->id,
                    'name' => $lineName,
                    "created_at" => now(),
                    "updated_at" => now(),
                    "deleted_at" => null,
                ]);
            }

            DB::commit();
            $newSection = Section::with(['lines'])->where('id', $section->id)->first();
            return response()->json([
                "message" => "Section created successfully",
                "data" => $newSection
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Internal Server Error",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $section_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|required',
            'lines' => 'array|required',
            'lines.*.id' => 'string',
            'lines.*.name' => 'string|required',
            'deleted_line_ids' => 'array',
            'deleted_line_ids.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            DB::beginTransaction();
            $section = Section::with(['lines'])->where('id', $section_id)->first();

            if (!$section) {
                return response()->json(['message' => 'Section not found'], 404);
            }

            Section::where('id', $section_id)->update([
                'name' => $request->name,
                'updated_at' => now()
            ]);

            foreach ($request['lines'] as $line) {
                $existingLine = null;

                if (isset($line['id'])) {
                    $existingLine = Line::where('section_id', $section_id)
                        ->where('id', $line['id'])
                        ->first();
                }

                if (!$existingLine) {
                    Line::create([
                        'section_id' => $section_id,
                        'name' => $line['name']
                    ]);
                } else {
                    Line::where('id', $line['id'])->update([
                        'name' => $line['name'],
                        'updated_at' => now()
                    ]);
                }
            }
            if (!empty($request['deleted_line_ids'])) {
                Line::whereIn('id', $request['deleted_line_ids'])->delete();
            }

            DB::commit();

            $newSection = Section::with(['lines'])->where('id', $section_id)->first();

            return response()->json([
                "message" => "Section updated successfully",
                "data" => $newSection
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Internal Server Error",
                "error" => $e->getMessage()
            ]);
        }
    }

    public function destroy($section_id)
    {
        $section = Section::find($section_id);
        if (!$section) {
            return response()->json([
                "message" => "Section not found"
            ], 404);
        }

        try {
            DB::beginTransaction();

            $section->delete();
            Line::where('section_id', $section_id)->delete();
            DB::commit();

            return response()->json([
                "message" => "Section deleted successfully"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Internal Server Error",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
