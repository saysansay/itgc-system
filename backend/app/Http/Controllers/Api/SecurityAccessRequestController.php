<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\SecurityAccessRequestsExport;
use App\Models\SecurityAccessRequest;
use App\Models\SecurityAccessAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SecurityAccessRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SecurityAccessRequest::with(['requestor', 'department', 'approver', 'attachments']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('requestor', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('requestor_id')) {
            $query->where('requestor_id', $request->requestor_id);
        }

        $items = $query->orderBy('requested_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($items);
    }

    public function export(Request $request)
    {
        $query = SecurityAccessRequest::with(['requestor', 'department', 'approver']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('requestor', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('requestor_id')) {
            $query->where('requestor_id', $request->requestor_id);
        }

        $items = $query->orderBy('requested_at', 'desc')->get();

        return Excel::download(new SecurityAccessRequestsExport($items), 'security-access-requests.xlsx');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requested_at' => 'nullable|date',
            'requestor_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'username' => 'required|string|max:255',
            'user_id_action' => 'nullable|in:new,change,delete',
            'password_action' => 'nullable|in:change,no_change',
            'email_action' => 'nullable|in:new,change,delete',
            'internet_access' => 'nullable|in:control_manager,control_staff',
            'file_sharing' => 'nullable|in:full_access,modify,read_only',
            'vpn_action' => 'nullable|in:new,change,delete',
            'reason' => 'nullable|string',
            'accpac_action' => 'nullable|in:new,change,delete',
            'ifs_action' => 'nullable|in:new,change,delete',
            'administrator_action' => 'nullable|in:new,change,delete',
            'restore' => 'nullable|boolean',
            'fingerprint_action' => 'nullable|in:new,change,delete',
            'change_data_action' => 'nullable|in:new,change,delete',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $requestNumber = SecurityAccessRequest::generateRequestNumber();

        $securityRequest = SecurityAccessRequest::create([
            'request_number' => $requestNumber,
            'requested_at' => now(),
            'requestor_id' => $request->requestor_id,
            'department_id' => $request->department_id,
            'username' => $request->username,
            'user_id_action' => $request->user_id_action,
            'password_action' => $request->password_action,
            'email_action' => $request->email_action,
            'internet_access' => $request->internet_access,
            'file_sharing' => $request->file_sharing,
            'vpn_action' => $request->vpn_action,
            'reason' => $request->reason,
            'accpac_action' => $request->accpac_action,
            'ifs_action' => $request->ifs_action,
            'administrator_action' => $request->administrator_action,
            'restore' => (bool) $request->restore,
            'fingerprint_action' => $request->fingerprint_action,
            'change_data_action' => $request->change_data_action,
            'notes' => $request->notes,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        $this->handleAttachments($request, $securityRequest);

        return response()->json([
            'message' => 'Security access request created successfully',
            'data' => $securityRequest->load(['requestor', 'department', 'attachments'])
        ], 201);
    }

    public function show($id)
    {
        $item = SecurityAccessRequest::with(['requestor', 'department', 'approver', 'attachments'])
            ->findOrFail($id);

        return response()->json(['data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = SecurityAccessRequest::findOrFail($id);

        if (!in_array($item->status, ['pending', 'rejected', 'approved'])) {
            return response()->json([
                'message' => 'Cannot update request with status: ' . $item->status
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'requested_at' => 'nullable|date',
            'requestor_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'username' => 'required|string|max:255',
            'user_id_action' => 'nullable|in:new,change,delete',
            'password_action' => 'nullable|in:change,no_change',
            'email_action' => 'nullable|in:new,change,delete',
            'internet_access' => 'nullable|in:control_manager,control_staff',
            'file_sharing' => 'nullable|in:full_access,modify,read_only',
            'vpn_action' => 'nullable|in:new,change,delete',
            'reason' => 'nullable|string',
            'accpac_action' => 'nullable|in:new,change,delete',
            'ifs_action' => 'nullable|in:new,change,delete',
            'administrator_action' => 'nullable|in:new,change,delete',
            'restore' => 'nullable|boolean',
            'fingerprint_action' => 'nullable|in:new,change,delete',
            'change_data_action' => 'nullable|in:new,change,delete',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,approved,rejected,completed',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = $request->status ?? $item->status;
        if ($item->status === 'approved' && $status !== 'approved' && $status !== 'completed') {
            return response()->json([
                'message' => 'Approved requests can only be marked as completed'
            ], 422);
        }

        $item->update([
            'requested_at' => $item->requested_at,
            'requestor_id' => $request->requestor_id,
            'department_id' => $request->department_id,
            'username' => $request->username,
            'user_id_action' => $request->user_id_action,
            'password_action' => $request->password_action,
            'email_action' => $request->email_action,
            'internet_access' => $request->internet_access,
            'file_sharing' => $request->file_sharing,
            'vpn_action' => $request->vpn_action,
            'reason' => $request->reason,
            'accpac_action' => $request->accpac_action,
            'ifs_action' => $request->ifs_action,
            'administrator_action' => $request->administrator_action,
            'restore' => (bool) $request->restore,
            'fingerprint_action' => $request->fingerprint_action,
            'change_data_action' => $request->change_data_action,
            'notes' => $request->notes,
            'status' => $status,
            'updated_by' => auth()->id(),
        ]);

        $this->handleAttachments($request, $item);

        return response()->json([
            'message' => 'Security access request updated successfully',
            'data' => $item->load(['requestor', 'department', 'attachments'])
        ]);
    }

    public function destroy($id)
    {
        $item = SecurityAccessRequest::findOrFail($id);

        if (!in_array($item->status, ['pending', 'rejected'])) {
            return response()->json([
                'message' => 'Cannot delete request with status: ' . $item->status
            ], 422);
        }

        $item->delete();

        return response()->json([
            'message' => 'Security access request deleted successfully'
        ]);
    }

    public function approve(Request $request, $id)
    {
        $item = SecurityAccessRequest::findOrFail($id);

        if ($item->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending requests can be approved'
            ], 422);
        }

        $item->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approval_date' => now(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Security access request approved successfully',
            'data' => $item->load(['requestor', 'department', 'approver', 'attachments'])
        ]);
    }

    public function reject(Request $request, $id)
    {
        $item = SecurityAccessRequest::findOrFail($id);

        if ($item->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending requests can be rejected'
            ], 422);
        }

        $item->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approval_date' => now(),
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Security access request rejected successfully',
            'data' => $item->load(['requestor', 'department', 'approver', 'attachments'])
        ]);
    }

    public function complete(Request $request, $id)
    {
        $item = SecurityAccessRequest::findOrFail($id);

        if ($item->status !== 'approved') {
            return response()->json([
                'message' => 'Only approved requests can be completed'
            ], 422);
        }

        $item->update([
            'status' => 'completed',
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Security access request completed successfully',
            'data' => $item->load(['requestor', 'department', 'approver', 'attachments'])
        ]);
    }

    public function deleteAttachment($id, $attachmentId)
    {
        $item = SecurityAccessRequest::findOrFail($id);
        $attachment = SecurityAccessAttachment::where('security_access_request_id', $item->id)
            ->where('id', $attachmentId)
            ->firstOrFail();

        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->json([
            'message' => 'Attachment deleted successfully'
        ]);
    }

    private function handleAttachments(Request $request, SecurityAccessRequest $item): void
    {
        if (!$request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            if (!$file) {
                continue;
            }

            $path = $file->store('security-access', 'public');

            SecurityAccessAttachment::create([
                'security_access_request_id' => $item->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }
    }
}
