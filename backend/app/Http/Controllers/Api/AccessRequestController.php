<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\AccessRequestsExport;
use App\Models\AccessRequest;
use App\Models\System;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AccessRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = AccessRequest::with(['user', 'system', 'approvals.approver']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by system
        if ($request->has('system_id')) {
            $query->where('system_id', $request->system_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by access type
        if ($request->has('access_type')) {
            $query->where('access_type', $request->access_type);
        }

        $accessRequests = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json($accessRequests);
    }

    public function export(Request $request)
    {
        $query = AccessRequest::with(['user', 'system', 'approver']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('system_id')) {
            $query->where('system_id', $request->system_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('access_type')) {
            $query->where('access_type', $request->access_type);
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(new AccessRequestsExport($items), 'access-requests.xlsx');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'system_id' => 'required|exists:systems,id',
            'access_type' => 'required|in:new,modify,revoke,temporary',
            'access_level' => 'required|in:read,write,admin,full',
            'purpose' => 'required|string',
            'justification' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $accessRequest = AccessRequest::create([
            'user_id' => auth()->id(),
            'system_id' => $request->system_id,
            'access_type' => $request->access_type,
            'access_level' => $request->access_level,
            'purpose' => $request->purpose,
            'justification' => $request->justification,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Access request created successfully',
            'data' => $accessRequest->load(['user', 'system'])
        ], 201);
    }

    public function show($id)
    {
        $accessRequest = AccessRequest::with(['user', 'system', 'approvals.approver'])
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $accessRequest
        ]);
    }

    public function update(Request $request, $id)
    {
        $accessRequest = AccessRequest::findOrFail($id);

        // Only allow update if status is pending or rejected
        if (!in_array($accessRequest->status, ['pending', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update access request with status: ' . $accessRequest->status
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'system_id' => 'required|exists:systems,id',
            'access_type' => 'required|in:new,modify,revoke,temporary',
            'access_level' => 'required|in:read,write,admin,full',
            'purpose' => 'required|string',
            'justification' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $accessRequest->update([
            'system_id' => $request->system_id,
            'access_type' => $request->access_type,
            'access_level' => $request->access_level,
            'purpose' => $request->purpose,
            'justification' => $request->justification,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Access request updated successfully',
            'data' => $accessRequest->load(['user', 'system'])
        ]);
    }

    public function destroy($id)
    {
        $accessRequest = AccessRequest::findOrFail($id);

        // Only allow delete if status is pending
        if ($accessRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete access request with status: ' . $accessRequest->status
            ], 403);
        }

        $accessRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Access request deleted successfully'
        ]);
    }
}
