<?php

namespace App\Http\Controllers\Web\MasterData;


use App\Models\SectionHead;
use App\Models\Stream;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\StreamSectionHead;
use App\Models\StreamVerification;
use App\Models\User;
use App\Models\UserHasStream;
use Tymon\JWTAuth\Facades\JWTAuth;


class StreamController extends Controller
{
    //
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);
        $order = $request->query('name', 'sort', 'asc');
        if ($order !== 'asc' && $order !== 'desc') {
            $order = 'asc';
        }
        $streams = Stream::where('name', 'ilike', "%$search%")
            ->select('id', 'name')
            ->orderBy('name', $order)
            ->paginate($limit);

        $pagination = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'totalRows' => $streams->total(),
            'totalPages' => $streams->lastPage(),
            'nextPage' => $streams->nextPageUrl(),
            'prevPage' => $streams->previousPageUrl(),
        ];

        $tableData = [
            'pagination' => $pagination,
            'data' => $streams->items(),
        ];

        return response()->json($tableData, 200);
    }

    public function show($stream_id)
    {
        $stream = Stream::with(['stream_section_head.section', 'stream_verification'])
            ->select('id', 'name')->find($stream_id);

        if (!$stream) {
            return response()->json(['message' => 'Stream not found'], 404);
        }

        return response()->json($stream, 200);
    }

    public function store(Request $request)
    {
        $token = Str::replaceFirst('Bearer ', '', $request->header('Authorization'));
        $user = JWTAuth::setToken($token)->authenticate();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            $stream = Stream::create(
                [
                    "name" => $request->input('name'),
                    "modified_by" =>  $user->name,
                ]
            );
            StreamVerification::create([
                'id' => Str::uuid()->toString(),
                'stream_id' => $stream->id,
                'type' =>  'verification_1',
                'name' => $request->input('stream_verification_name_1'),
                'user_id' => null,
                "modified_by" =>  $user->name,
            ]);

            StreamVerification::create([
                'id' => Str::uuid()->toString(),
                'stream_id' => $stream->id,
                'type' =>  'verification_2',
                'name' => $request->input('stream_verification_name_2'),
                'user_id' => null,
                "modified_by" =>  $user->name,
            ]);

            foreach ($request->input('section_id') as $section_id) {
                # code...
                StreamSectionHead::create([
                    'stream_id' => $stream->id,
                    'section_id' => $section_id,
                    'user_id' => null,
                    "modified_by" =>  $user->name,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Success Created Stream'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Internal server error",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $stream_id)
    {
        $token = Str::replaceFirst('Bearer ', '', $request->header('Authorization'));
        $user = JWTAuth::setToken($token)->authenticate();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $stream = Stream::findOrFail($stream_id);
            if (!$stream) {
                return response()->json([
                    "message" => "Stream not found"
                ], 404);
            }
            $stream->update([
                'name' => $request->input('name'),
                "modified_by" =>  $user->name,

            ]);


            $verification1 = StreamVerification::where('stream_id', $stream_id)
                ->where('type', 'verification_1')
                ->firstOrFail();
            $verification1->update([
                'name' => $request->input('stream_verification_name_1'),
                "modified_by" =>  $user->name,

            ]);

            $verification2 = StreamVerification::where('stream_id', $stream_id)
                ->where('type', 'verification_2')
                ->firstOrFail();
            $verification2->update([
                'name' => $request->input('stream_verification_name_2'),
                "modified_by" =>  $user->name,

            ]);

            StreamSectionHead::where('stream_id', $stream_id)->delete();

            foreach ($request->input('section_id') as $section_id) {
                StreamSectionHead::create([
                    'stream_id' => $stream_id,
                    'section_id' => $section_id,
                    'user_id' => null,
                    "modified_by" =>  $user->name,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Success Created Stream'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Internal server error",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $stream_id)
    {
        # code...
        try {
            $token = Str::replaceFirst('Bearer ', '', $request->header('Authorization'));
            $user = JWTAuth::setToken($token)->authenticate();
            $stream = Stream::findOrFail($stream_id);

            if (!$stream) {
                return response()->json([
                    "message" => "Stream not found"
                ], 404);
            }
            $stream->update([
                "modified_by" =>  $user->name,
            ]);
            $stream->delete();
            return response()->json(['message' => 'Success Deleted Stream'], 200);
        } catch (\Exception $e) {
            //throw $th;
            return response()->json([
                "message" => "Internal server error",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
