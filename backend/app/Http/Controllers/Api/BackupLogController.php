<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\BackupLogsExport;
use App\Models\BackupLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class BackupLogController extends Controller
{
    public function index(Request $request)
    {
        $query = BackupLog::with(['system', 'verifier']);

        // Search by system name or backup location
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('system', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('backup_location', 'like', "%{$search}%");
            });
        }

        // Filter by system
        if ($request->has('system_id')) {
            $query->where('system_id', $request->system_id);
        }

        // Filter by backup type
        if ($request->has('backup_type')) {
            $query->where('backup_type', $request->backup_type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by verification status
        if ($request->has('is_verified')) {
            $query->where('is_verified', $request->is_verified);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('scheduled_time', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('scheduled_time', '<=', $request->end_date);
        }

        $backupLogs = $query->orderBy('scheduled_time', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json($backupLogs);
    }

    public function export(Request $request)
    {
        $query = BackupLog::with(['system', 'verifier']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('system', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('backup_location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('system_id')) {
            $query->where('system_id', $request->system_id);
        }

        if ($request->filled('backup_type')) {
            $query->where('backup_type', $request->backup_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->is_verified);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('scheduled_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('scheduled_time', '<=', $request->end_date);
        }

        $items = $query->orderBy('scheduled_time', 'desc')->get();

        return Excel::download(new BackupLogsExport($items), 'backup-logs.xlsx');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'system_id' => 'required|exists:systems,id',
            'backup_type' => 'required|in:Full,Incremental,Differential',
            'scheduled_time' => 'required|date',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'status' => 'nullable|in:scheduled,in_progress,success,failed',
            'backup_location' => 'nullable|string|max:500',
            'backup_size' => 'nullable|integer|min:0',
            'error_message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $backupLog = BackupLog::create([
            'system_id' => $request->system_id,
            'backup_type' => $request->backup_type,
            'scheduled_time' => $request->scheduled_time,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status ?? 'scheduled',
            'backup_location' => $request->backup_location,
            'backup_size' => $request->backup_size,
            'error_message' => $request->error_message,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Backup log created successfully',
            'data' => $backupLog->load(['system', 'verifier'])
        ], 201);
    }

    public function show($id)
    {
        $backupLog = BackupLog::with(['system', 'verifier'])->findOrFail($id);
        return response()->json(['data' => $backupLog]);
    }

    public function update(Request $request, $id)
    {
        $backupLog = BackupLog::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'system_id' => 'required|exists:systems,id',
            'backup_type' => 'required|in:Full,Incremental,Differential',
            'scheduled_time' => 'required|date',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'status' => 'nullable|in:scheduled,in_progress,success,failed',
            'backup_location' => 'nullable|string|max:500',
            'backup_size' => 'nullable|integer|min:0',
            'error_message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $backupLog->update([
            'system_id' => $request->system_id,
            'backup_type' => $request->backup_type,
            'scheduled_time' => $request->scheduled_time,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => $request->status ?? $backupLog->status,
            'backup_location' => $request->backup_location,
            'backup_size' => $request->backup_size,
            'error_message' => $request->error_message,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Backup log updated successfully',
            'data' => $backupLog->load(['system', 'verifier'])
        ]);
    }

    public function destroy($id)
    {
        $backupLog = BackupLog::findOrFail($id);
        $backupLog->delete();

        return response()->json([
            'message' => 'Backup log deleted successfully'
        ]);
    }

    public function verify(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_verified' => 'required|boolean',
            'verification_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $backupLog = BackupLog::findOrFail($id);

        $backupLog->update([
            'is_verified' => $request->is_verified,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'verification_notes' => $request->verification_notes,
        ]);

        return response()->json([
            'message' => 'Backup log verification updated successfully',
            'data' => $backupLog->load(['system', 'verifier'])
        ]);
    }
}
