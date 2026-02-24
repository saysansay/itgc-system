<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApprovalController extends Controller
{
    public function show($token)
    {
        $approval = Approval::with(['approvable', 'approver'])
            ->where('approval_token', $token)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $approval
        ]);
    }

    public function approve(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'comments' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $approval = Approval::with('approvable')
            ->where('approval_token', $token)
            ->firstOrFail();

        if ($approval->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This approval has already been processed'
            ], 422);
        }

        $approval->update([
            'status' => 'approved',
            'comments' => $request->comments ?? $approval->comments,
            'responded_at' => now(),
            'responded_ip' => $request->ip(),
        ]);

        $this->updateApprovableStatus($approval, 'approved', $request->comments);

        return response()->json([
            'success' => true,
            'message' => 'Approval recorded successfully',
            'data' => $approval->fresh(['approvable', 'approver'])
        ]);
    }

    public function reject(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'comments' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $approval = Approval::with('approvable')
            ->where('approval_token', $token)
            ->firstOrFail();

        if ($approval->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This approval has already been processed'
            ], 422);
        }

        $approval->update([
            'status' => 'rejected',
            'comments' => $request->comments ?? $approval->comments,
            'responded_at' => now(),
            'responded_ip' => $request->ip(),
        ]);

        $this->updateApprovableStatus($approval, 'rejected', $request->comments);

        return response()->json([
            'success' => true,
            'message' => 'Rejection recorded successfully',
            'data' => $approval->fresh(['approvable', 'approver'])
        ]);
    }

    private function updateApprovableStatus(Approval $approval, string $decision, ?string $comments): void
    {
        $approvable = $approval->approvable;

        if (!$approvable) {
            return;
        }

        if ($decision === 'approved') {
            $hasPending = $approvable->approvals()
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                return;
            }
        }

        $attributes = $approvable->getAttributes();
        $updates = [];

        if (array_key_exists('status', $attributes)) {
            $updates['status'] = $decision === 'approved' ? 'approved' : 'rejected';
        }

        if ($decision === 'approved') {
            if (array_key_exists('approved_by', $attributes)) {
                $updates['approved_by'] = $approval->approver_id;
            }
            if (array_key_exists('approved_at', $attributes)) {
                $updates['approved_at'] = now();
            }
        }

        if ($decision === 'rejected' && array_key_exists('rejection_reason', $attributes)) {
            $updates['rejection_reason'] = $comments;
        }

        if (array_key_exists('updated_by', $attributes)) {
            $updates['updated_by'] = $approval->approver_id;
        }

        if (!empty($updates)) {
            $approvable->update($updates);
        }
    }
}
