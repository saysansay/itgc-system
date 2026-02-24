<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ChangeRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/my-approvals', [DashboardController::class, 'myApprovals']);

    // Change Requests
    Route::get('/change-requests/export', [ChangeRequestController::class, 'export']);
    Route::apiResource('change-requests', ChangeRequestController::class);
    Route::post('/change-requests/{id}/submit', [ChangeRequestController::class, 'submitForApproval']);
    Route::post('/change-requests/{id}/evidence', [ChangeRequestController::class, 'uploadEvidence']);

    // Master Data - Users
    Route::get('/roles/list', [\App\Http\Controllers\Api\UserController::class, 'getRoles']);
    Route::get('/departments/list', [\App\Http\Controllers\Api\UserController::class, 'getDepartments']);
    Route::apiResource('users', \App\Http\Controllers\Api\UserController::class);
    
    // Master Data - Roles
    Route::get('/permissions/list', [\App\Http\Controllers\Api\RoleController::class, 'getPermissions']);
    Route::apiResource('roles', \App\Http\Controllers\Api\RoleController::class);
    
    // Master Data - Permissions
    Route::apiResource('permissions', \App\Http\Controllers\Api\PermissionController::class);
    
    // Master Data - Systems
    Route::apiResource('systems', \App\Http\Controllers\Api\SystemController::class);
    
    // Master Data - Departments
    Route::apiResource('departments', \App\Http\Controllers\Api\DepartmentController::class);

    // Access Requests
    Route::get('/access-requests/export', [\App\Http\Controllers\Api\AccessRequestController::class, 'export']);
    Route::apiResource('access-requests', \App\Http\Controllers\Api\AccessRequestController::class);

    // Backup Logs
    Route::get('/backup-logs/export', [\App\Http\Controllers\Api\BackupLogController::class, 'export']);
    Route::apiResource('backup-logs', \App\Http\Controllers\Api\BackupLogController::class);
    Route::post('/backup-logs/{id}/verify', [\App\Http\Controllers\Api\BackupLogController::class, 'verify']);

    // IT Assets
    Route::get('/it-assets/export', [\App\Http\Controllers\Api\ItAssetController::class, 'export']);
    Route::apiResource('it-assets', \App\Http\Controllers\Api\ItAssetController::class);
    Route::post('/it-assets/{id}/assign', [\App\Http\Controllers\Api\ItAssetController::class, 'assign']);
    Route::post('/it-assets/{id}/return', [\App\Http\Controllers\Api\ItAssetController::class, 'return']);

    // USB Loans
    Route::get('/usb-loans/it-admin-users', [\App\Http\Controllers\Api\UsbLoanController::class, 'getItAdminUsers']);
    Route::get('/usb-loans/export', [\App\Http\Controllers\Api\UsbLoanController::class, 'export']);
    Route::apiResource('usb-loans', \App\Http\Controllers\Api\UsbLoanController::class);
    Route::post('/usb-loans/{id}/approve', [\App\Http\Controllers\Api\UsbLoanController::class, 'approve']);
    Route::post('/usb-loans/{id}/reject', [\App\Http\Controllers\Api\UsbLoanController::class, 'reject']);
    Route::post('/usb-loans/{id}/return', [\App\Http\Controllers\Api\UsbLoanController::class, 'returnUsb']);

    // Admin Access Requests
    Route::get('/admin-access-requests/export', [\App\Http\Controllers\Api\AdminAccessRequestController::class, 'export']);
    Route::apiResource('admin-access-requests', \App\Http\Controllers\Api\AdminAccessRequestController::class);
    Route::post('/admin-access-requests/{id}/approve', [\App\Http\Controllers\Api\AdminAccessRequestController::class, 'approve']);
    Route::post('/admin-access-requests/{id}/reject', [\App\Http\Controllers\Api\AdminAccessRequestController::class, 'reject']);

    // Security Access Requests
    Route::get('/security-access-requests/export', [\App\Http\Controllers\Api\SecurityAccessRequestController::class, 'export']);
    Route::apiResource('security-access-requests', \App\Http\Controllers\Api\SecurityAccessRequestController::class);
    Route::post('/security-access-requests/{id}/approve', [\App\Http\Controllers\Api\SecurityAccessRequestController::class, 'approve']);
    Route::post('/security-access-requests/{id}/reject', [\App\Http\Controllers\Api\SecurityAccessRequestController::class, 'reject']);
    Route::post('/security-access-requests/{id}/complete', [\App\Http\Controllers\Api\SecurityAccessRequestController::class, 'complete']);
    Route::delete('/security-access-requests/{id}/attachments/{attachmentId}', [\App\Http\Controllers\Api\SecurityAccessRequestController::class, 'deleteAttachment']);

    // General Trouble
    Route::get('/general-troubles/export', [\App\Http\Controllers\Api\GeneralTroubleController::class, 'export']);
    Route::apiResource('general-troubles', \App\Http\Controllers\Api\GeneralTroubleController::class);

    // Audit Logs
    Route::get('/audit-logs', [\App\Http\Controllers\Api\AuditLogController::class, 'index']);
    Route::post('/audit-logs/export', [\App\Http\Controllers\Api\AuditLogController::class, 'export']);
});

// Approval (without authentication - token-based)
Route::get('/approval/{token}', [\App\Http\Controllers\Api\ApprovalController::class, 'show']);
Route::post('/approval/{token}/approve', [\App\Http\Controllers\Api\ApprovalController::class, 'approve']);
Route::post('/approval/{token}/reject', [\App\Http\Controllers\Api\ApprovalController::class, 'reject']);
