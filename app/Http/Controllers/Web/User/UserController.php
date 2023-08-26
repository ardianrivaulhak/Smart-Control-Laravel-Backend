<?php

namespace App\Http\Controllers\Web\User;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Stream;
use App\Models\StreamSectionHead;
use App\Models\StreamVerification;
use App\Models\UserHasStream;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpParser\Node\Stmt\Echo_;

class UserController extends Controller
{
    //

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $users = User::where('name', 'ilike', '%' . $search . '%')
            ->with(['role', 'streams'])
            ->paginate($limit, ['id', 'is_active', 'npk', 'name', 'role_id']);


        $tableData = [
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'data' => $users->items(),
            'totalRows' => $users->total(),
            'totalPages' => $users->lastPage(),
            'nextPage' => $users->nextPageUrl(),
            'prevPage' => $users->previousPageUrl(),
        ];

        return response()->json($tableData, 200);
    }


    public function show($user_id)
    {
        # code...


        $user = User::with(['role', 'streams'])->where('id', $user_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'message' => 'User read successfully',
            'user' => $user,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'npk' => 'required|string',
            'email' => ['required', Rule::unique('users')->whereNull('deleted_at')],
            'password' => 'required|string|min:6',
            'name' => 'required|string',
            'is_active' => 'boolean',
            'role_id' => 'required|string|exists:roles,id',
            'stream_id' => 'required|string|exists:streams,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }


        $photo_url = "";

        if ($request->has('photo_url')) {
            $photo = $request->file("photo_url");
            $photo_url = $photo->store("user", "public");
        }

        $user = User::create([
            'npk' => $request->npk,
            'role_id' => $request->role_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_active' => $request->is_active,
            'photo_url' => $photo_url
        ]);

        UserHasStream::create([
            'user_id' => $request->input('user_id', $user->id),
            'stream_id' => $request->input('stream_id')
        ]);


        return response()->json([
            'message' => 'User successfully created',
            'user' => $user
        ], 201);
    }

    public function update(Request $request, $user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'npk' => 'required|string',
            'password' => 'string|min:6',
            'name' => 'required|string',
            'role_id' => 'required|string|exists:roles,id',
            'stream_id' => 'required|string|exists:streams,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $photo_url = $user->photo_url;

        if ($request->has('photo_url')) {
            $photo = $request->file("photo_url");
            $photo_url = $photo->store("user", "public");
        }

        $user->update([
            'npk' => $request->npk,
            'role_id' => $request->role_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->has('password') ? bcrypt($request->password) : $user->password,
            'is_active' => $request->is_active,
            'photo_url' => $photo_url,
        ]);

        UserHasStream::updateOrCreate(
            ['user_id' => $user_id],
            ['stream_id' => $request->input('stream_id')]
        );

        return response()->json([
            'message' => 'User successfully updated',
            'user' => $user
        ], 200);
    }


    public function updatePassword(Request $request, $user_id)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }


        $user = DB::table('users')->where('id', $user_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        DB::table('users')->where('id', $user_id)->update([
            'password' => bcrypt($request->input('password'))
        ]);

        return response()->json(['message' => 'Password successfully updated'], 200);
    }

    public function getRoleApproval(Request $request)
    {
        try {
            $stream_id = $request->input('stream_id');

            $users = User::with(['stream_verifications', 'stream_section_head'])
                ->when($stream_id, function ($query, $stream_id) {
                    return $query->whereHas('stream_verifications', function ($subQuery) use ($stream_id) {
                        $subQuery->where('stream_id', $stream_id);
                    });
                })
                ->select('id', 'name')
                ->get();

            return response()->json([
                'message' => "Successfuly Read Data",
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Internal Server Error",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listRoleApprovalByStream(Request $request)
    {
        $streamId = $request->stream_id;
        $streams = Stream::with(['stream_verification' => function ($query) {
            $query->with(['user' => function ($query) {
                $query->with(['role' => function ($query) {
                    $query->where('name', 'Reviewer');
                }]);
            }]);
        }, 'stream_section_head' => function ($query) {
            $query->with(['section', 'user' => function ($query) {
                $query->with(['role' => function ($query) {
                    $query->where('name', 'Reviewer');
                }]);
            }]);
        }])
            ->where('id', $streamId)
            ->get();

        return $streams;
    }


    public function updateStatus(Request $request, $user_id)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }


        $user = DB::table('users')->where('id', $user_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        DB::table('users')->where('id', $user_id)->update([
            'is_active' => $request->input("is_active")
        ]);

        return response()->json(['message' => 'Is_Active successfully updated'], 200);
    }

    public function updateRoleApproval(Request $request)
    {
        try {
            DB::beginTransaction();

            $user_id = $request->input('user_id');
            $stream_verification_id =  $request->input("stream_verification_id");
            $stream_section_head_id =  $request->input("stream_section_head_id");

            $user = User::with('role')
                ->where('id', $user_id)
                ->first();

            $role = $user->role;
            if ($role->name === "Reviewer") {
                # code...
                if (!empty($stream_section_head_id)) {
                    StreamSectionHead::whereIn('id', $stream_section_head_id)->update([
                        'user_id' => $user_id ?: null
                    ]);
                }

                if (!empty($stream_verification_id)) {
                    StreamVerification::whereIn('id', $stream_verification_id)->update([
                        'user_id' => $user_id ?: null
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => "Update role approval success",
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => "Internal Server Error",
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateProfile(Request $request, $user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                Rule::unique('users')->whereNull('deleted_at')->ignore($user_id),
            ],
            'name' => [
                'required',
                'string',
            ],
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $photo_url = $user->photo_url;

        if ($request->has('photo_url')) {
            $photo = $request->file("photo_url");
            $photo_url = $photo->store("user", "public");
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'photo_url' => $photo_url,
        ]);

        return response()->json([
            'message' => 'User successfully updated',
            'user' => $user
        ], 200);
    }

    public function destroy($user_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User successfully deleted'], 200);
    }
}
