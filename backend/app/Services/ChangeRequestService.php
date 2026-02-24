<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Repositories\ChangeRequestRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChangeRequestService
{
    protected $repository;

    public function __construct(ChangeRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            // Set requester
            $data['requester_id'] = auth()->id();
            $data['created_by'] = auth()->id();

            $changeRequest = $this->repository->create($data);

            // Log activity
            AuditLog::logActivity(
                'create',
                'change_requests',
                "Created change request: {$changeRequest->ticket_number}",
                get_class($changeRequest),
                $changeRequest->id,
                null,
                $data
            );

            DB::commit();
            return $changeRequest;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $changeRequest = $this->repository->findOrFail($id);
            $oldData = $changeRequest->toArray();

            $data['updated_by'] = auth()->id();
            $changeRequest->update($data);

            // Log activity
            AuditLog::logActivity(
                'update',
                'change_requests',
                "Updated change request: {$changeRequest->ticket_number}",
                get_class($changeRequest),
                $changeRequest->id,
                $oldData,
                $data
            );

            DB::commit();
            return $changeRequest->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $changeRequest = $this->repository->findOrFail($id);

            // Log activity
            AuditLog::logActivity(
                'delete',
                'change_requests',
                "Deleted change request: {$changeRequest->ticket_number}",
                get_class($changeRequest),
                $changeRequest->id
            );

            $this->repository->delete($id);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function submitForApproval($id)
    {
        DB::beginTransaction();
        try {
            $changeRequest = $this->repository->findOrFail($id);

            // Update status
            $changeRequest->update([
                'status' => 'pending_approval',
                'updated_by' => auth()->id(),
            ]);

            // Create approval workflow
            $this->createApprovalWorkflow($changeRequest);

            // Log activity
            AuditLog::logActivity(
                'submit',
                'change_requests',
                "Submitted change request for approval: {$changeRequest->ticket_number}",
                get_class($changeRequest),
                $changeRequest->id
            );

            DB::commit();
            return $changeRequest;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function uploadEvidence($changeRequestId, $file, $description = null)
    {
        DB::beginTransaction();
        try {
            $changeRequest = $this->repository->findOrFail($changeRequestId);

            // Store file
            $path = $file->store('change-evidences', 'public');

            // Create evidence record
            $evidence = $changeRequest->evidences()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'description' => $description,
                'uploaded_by' => auth()->id(),
            ]);

            // Log activity
            AuditLog::logActivity(
                'upload',
                'change_requests',
                "Uploaded evidence for change request: {$changeRequest->ticket_number}",
                get_class($changeRequest),
                $changeRequest->id
            );

            DB::commit();
            return $evidence;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function createApprovalWorkflow($changeRequest)
    {
        // Create approval record
        // Level 1: IT Admin
        $changeRequest->approvals()->create([
            'level' => 1,
            'approver_id' => $this->getItAdminId(), // Get from settings
            'status' => 'pending',
        ]);

        // If high risk, add Level 2: Super Admin
        if ($changeRequest->risk_level === 'high') {
            $changeRequest->approvals()->create([
                'level' => 2,
                'approver_id' => $this->getSuperAdminId(),
                'status' => 'pending',
            ]);
        }

        // TODO: Send email notification to approvers
    }

    protected function getItAdminId()
    {
        // Get first IT Admin user
        return \App\Models\User::whereHas('roles', function ($q) {
            $q->where('slug', 'it-admin');
        })->first()->id ?? 1;
    }

    protected function getSuperAdminId()
    {
        // Get first Super Admin user
        return \App\Models\User::whereHas('roles', function ($q) {
            $q->where('slug', 'super-admin');
        })->first()->id ?? 1;
    }
}
