<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\AdminAccessRequestsExport;
use App\Models\AdminAccessRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AdminAccessRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminAccessRequest::with(['requestor', 'department', 'approver']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhere('hostname', 'like', "%{$search}%")
                    ->orWhere('user_administrator', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('requestor', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        if ($request->has('method')) {
            $query->where('method', $request->method);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $requests = $query->orderBy('requested_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($requests);
    }

    public function export(Request $request)
    {
        $query = AdminAccessRequest::with(['requestor', 'department', 'approver']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhere('hostname', 'like', "%{$search}%")
                    ->orWhere('user_administrator', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('requestor', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $items = $query->orderBy('requested_at', 'desc')->get();

        return Excel::download(new AdminAccessRequestsExport($items), 'admin-access-requests.xlsx');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requestor_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'request_type' => 'required|in:temporary,permanent,emergency,maintenance',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:hour,day',
            'method' => 'required|in:vpn,rdp,local,server_console,others',
            'hostname' => 'required|string|max:255',
            'user_administrator' => 'required|string|max:255',
            'purpose' => 'required|string',
            'requested_at' => 'nullable|date',
            'partner' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $requestNumber = AdminAccessRequest::generateRequestNumber();

        $adminAccessRequest = AdminAccessRequest::create([
            'request_number' => $requestNumber,
            'requestor_id' => $request->requestor_id,
            'department_id' => $request->department_id,
            'request_type' => $request->request_type,
            'duration_value' => $request->duration_value,
            'duration_unit' => $request->duration_unit,
            'method' => $request->method,
            'hostname' => $request->hostname,
            'user_administrator' => $request->user_administrator,
            'purpose' => $request->purpose,
            'requested_at' => $request->requested_at ?? now(),
            'partner' => $request->partner,
            'notes' => $request->notes,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Admin access request created successfully',
            'data' => $adminAccessRequest->load(['requestor', 'department'])
        ], 201);
    }

    public function show($id)
    {
        $requestModel = AdminAccessRequest::with(['requestor', 'department', 'approver'])
            ->findOrFail($id);

        return response()->json(['data' => $requestModel]);
    }

    public function update(Request $request, $id)
    {
        $requestModel = AdminAccessRequest::findOrFail($id);

        if (!in_array($requestModel->status, ['pending', 'rejected'])) {
            return response()->json([
                'message' => 'Cannot update request with status: ' . $requestModel->status
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'requestor_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'request_type' => 'required|in:temporary,permanent,emergency,maintenance',
            'duration_value' => 'required|integer|min:1',
            'duration_unit' => 'required|in:hour,day',
            'method' => 'required|in:vpn,rdp,local,server_console,others',
            'hostname' => 'required|string|max:255',
            'user_administrator' => 'required|string|max:255',
            'purpose' => 'required|string',
            'requested_at' => 'nullable|date',
            'partner' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $requestModel->update([
            'requestor_id' => $request->requestor_id,
            'department_id' => $request->department_id,
            'request_type' => $request->request_type,
            'duration_value' => $request->duration_value,
            'duration_unit' => $request->duration_unit,
            'method' => $request->method,
            'hostname' => $request->hostname,
            'user_administrator' => $request->user_administrator,
            'purpose' => $request->purpose,
            'requested_at' => $request->requested_at ?? $requestModel->requested_at,
            'partner' => $request->partner,
            'notes' => $request->notes,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Admin access request updated successfully',
            'data' => $requestModel->load(['requestor', 'department'])
        ]);
    }

    public function destroy($id)
    {
        $requestModel = AdminAccessRequest::findOrFail($id);

        if (!in_array($requestModel->status, ['pending', 'rejected'])) {
            return response()->json([
                'message' => 'Cannot delete request with status: ' . $requestModel->status
            ], 422);
        }

        $requestModel->delete();

        return response()->json([
            'message' => 'Admin access request deleted successfully'
        ]);
    }

    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $requestModel = AdminAccessRequest::findOrFail($id);

        if ($requestModel->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending requests can be approved'
            ], 422);
        }

        $requestModel->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $request->notes ?? $requestModel->notes,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Admin access request approved successfully',
            'data' => $requestModel->load(['requestor', 'department', 'approver'])
        ]);
    }

    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $requestModel = AdminAccessRequest::findOrFail($id);

        if ($requestModel->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending requests can be rejected'
            ], 422);
        }

        $requestModel->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $request->notes ?? $requestModel->notes,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Admin access request rejected successfully',
            'data' => $requestModel->load(['requestor', 'department', 'approver'])
        ]);
    }
}
