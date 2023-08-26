<?php

use App\Http\Controllers\Mobile\CpiOrder\CpiOrderController;
use App\Http\Controllers\Mobile\CpiOrder\DocumentController;
use App\Http\Controllers\Web\User\AccessController;
use App\Http\Controllers\Web\Login\AuthController;
use App\Http\Controllers\Mobile\Login\AuthMobileController;
use App\Http\Controllers\Mobile\CpiOrder\UploadController;
use App\Http\Controllers\Mobile\History\HistoryController;
use App\Http\Controllers\Mobile\Notification\NotificationController;
use App\Http\Controllers\Mobile\Overview\OverviewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\MasterData\FormController;
use App\Http\Controllers\Web\User\PermissionController;
use App\Http\Controllers\Web\User\RoleController;
use App\Http\Controllers\Web\MasterData\SectionController;
use App\Http\Controllers\Web\MasterData\StreamController;
use App\Http\Controllers\Web\User\UserController;
use App\Http\Controllers\Mobile\User\UserMobileController;
use App\Http\Controllers\Web\Approval\ApprovalController;
use App\Http\Controllers\Web\Dashboard\DashboardController;
use App\Http\Controllers\Web\Report\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::group(['prefix' => 'web/auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/me', [AuthController::class, 'me'])
        ->middleware(['auth']);
});

Route::group(['prefix' => 'mobile/auth'], function () {
    Route::post('/login', [AuthMobileController::class, 'login']);
});


Route::group(['prefix' => 'web/users', 'middleware' => ['auth']], function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/approval', [UserController::class, 'getRoleApproval']);
    Route::put('/approval', [UserController::class, 'updateRoleApproval']);
    Route::get('/workflow', [UserController::class, 'listRoleApprovalByStream']);
    Route::get('/{user_id}', [UserController::class, 'show']);
    Route::post('/{user_id}', [UserController::class, 'update']);
    Route::post('/profile/{user_id}', [UserController::class, 'updateProfile']);
    Route::put('/status/{user_id}', [UserController::class, 'updateStatus']);
    Route::put('/{user_id}', [UserController::class, 'updatePassword']);
    Route::delete('/{user_id}', [UserController::class, 'destroy']);
});


Route::group(['prefix' => 'web/access', 'middleware' => ['auth']], function () {
    Route::get('/', [AccessController::class, 'index']);
    Route::post('/', [AccessController::class, 'store']);
    Route::get('/{access_id}', [AccessController::class, 'show']);
    Route::put('/{access_id}', [AccessController::class, 'update']);
    Route::delete('/{role_id}', [AccessController::class, 'destroy']);
});

Route::group(['prefix' => 'web/permission', 'middleware' => ['auth']], function () {
    Route::get('/', [PermissionController::class, 'index']);
    Route::post('/', [PermissionController::class, 'store']);
    Route::get('/{permission_id}', [PermissionController::class, 'show']);
    Route::put('/{permission_id}', [PermissionController::class, 'update']);
    Route::delete('/{role_id}', [PermissionController::class, 'destroy']);
});

Route::group(['prefix' => 'web/role', 'middleware' => ['auth']], function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::post('/', [RoleController::class, 'store']);
    Route::get('/{role_id}', [RoleController::class, 'show']);
    Route::get('/{role_id}/detail-access', [RoleController::class, 'findByIdWithAccessAndPermission']);
    Route::put('/{role_id}', [RoleController::class, 'update']);
    Route::delete('/{role_id}', [RoleController::class, 'destroy']);
    Route::put('/{role_id}/assign', [RoleController::class, 'assignAccessAndPermission']);
});

Route::group(['prefix' => 'web/section', 'middleware' => ['auth']], function () {
    Route::get('/', [SectionController::class, 'index']);
    Route::get('/list-for-params', [SectionController::class, 'listForParams']);
    Route::post('/', [SectionController::class, 'store']);
    Route::get('/{section_id}', [SectionController::class, 'show']);
    Route::put('/{section_id}', [SectionController::class, 'update']);
    Route::delete('/{section_id}', [SectionController::class, 'destroy']);
});

Route::post('file/upload', [UploadController::class, 'store']);

Route::group(['prefix' => 'web/form', 'middleware' => ['auth']], function () {
    Route::get('/', [FormController::class, 'index']);
    Route::post('/', [FormController::class, 'store']);
    Route::get('/log-trails', [FormController::class, 'logTrails']);
});

Route::group(['prefix' => 'web/stream', 'middleware' => ['auth']], function () {
    Route::get('/', [StreamController::class, 'index']);
    Route::get('/{stream_id}', [StreamController::class, 'show']);
    Route::post('/', [StreamController::class, 'store']);
    Route::put('/{stream_id}', [StreamController::class, 'update']);
    Route::delete('/{stream_id}', [StreamController::class, 'destroy']);
});

Route::group(['middleware' => ['auth'], 'prefix' => 'mobile/cpi-order'], function () {
    Route::get('/', [CpiOrderController::class, 'index']);
    Route::post('/', [CpiOrderController::class, 'store']);
    Route::post('/exit', [CpiOrderController::class, 'exit']);
    Route::get('/{cpi_order_id}', [CpiOrderController::class, 'show']);
});

Route::group(['middleware' => ['auth'], 'prefix' => 'mobile/history'], function () {
    Route::get('/', [HistoryController::class, 'historyList']);
    Route::get('/on-going', [HistoryController::class, 'index']);
    Route::get('/{cpi_order_id}', [HistoryController::class, 'show']);
    Route::delete('/{cpi_order_exit_id}', [HistoryController::class, 'deleteOnGoingDocument']);
});

Route::group(['middleware' => ['auth'], 'prefix' => 'mobile/user'], function () {
    Route::get('/', [UserMobileController::class, 'index']);
    Route::post('/update-password', [UserMobileController::class, 'updatePassword']);
    Route::post('/update-photo', [UserMobileController::class, 'updatePhoto']);
});

Route::group(['middleware' => ['auth'], 'prefix' => 'web/approval'], function () {
    Route::get('/', [ApprovalController::class, 'index']);
    Route::get('/{cpi_order_id}', [ApprovalController::class, 'show']);
    Route::post('/approve', [ApprovalController::class, 'approve']);
    Route::post('/decline', [ApprovalController::class, 'declined']);
});

Route::group(['middleware' => ['auth'], 'prefix' => 'web/report'], function () {
    Route::get('/claim', [ReportController::class, 'indexClaim']);
    Route::get('/procedure', [ReportController::class, 'indexProcedure']);
    Route::get('/pokayoke', [ReportController::class, 'indexPokayoke']);
    Route::get('/logtrails', [ReportController::class, 'indexLogTrailsDeclineds']);
    Route::get('/download/csv/claim', [ReportController::class, 'downloadCsvClaim']);
    Route::get('/download/csv/procedure', [ReportController::class, 'downloadCsvProcedure']);
    Route::get('/download/csv/pokayoke', [ReportController::class, 'downloadCsvPokayoke']);
    Route::get('/{cpi_order_id}', [ReportController::class, 'show']);
});

Route::group(['middleware' => ['auth'], 'prefix' => 'mobile/overview'], function () {
    Route::get('/', [OverviewController::class, 'index']);
});


Route::group(['middleware' => ['auth'], 'prefix' => 'web/dashboard'], function () {
    Route::get('/general', [DashboardController::class, 'index']);
    Route::get('/information', [DashboardController::class, 'cpiInformation']);
    Route::get('/sumary', [DashboardController::class, 'sumary']);
    Route::get('/history', [DashboardController::class, 'historyNg']);
});

Route::group(['middleware' => ['auth'], 'prefix' => 'mobile/notification'], function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/{cpi_order_id}', [NotificationController::class, 'show']);
});

Route::group(['middleware' => ['auth'], 'prefix' => 'mobile/document'], function () {
    Route::get('/list-for-params', [DocumentController::class, 'index']);
});
